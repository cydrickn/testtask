<?php
declare(strict_types=1);

namespace Tests\App\Unit\Services\MailChimp;

use App\Database\Entities\MailChimp\MailChimpList;
use App\Database\Entities\MailChimp\MailChimpListMember;
use App\Services\MailChimp\ListMemberService;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Mailchimp\Mailchimp;
use Mockery;
use Mockery\MockInterface;
use Tests\App\TestCases\TestCase;

/**
 * @author Cydrick Nonog <cydrick.dev@gmail.com>
 */
class ListMemberServiceTest extends TestCase
{
    public function testAddMemberToListValidationException()
    {
        $this->expectException(ValidationException::class);

        $listMemberService = new ListMemberService(
            Mockery::mock(EntityManagerInterface::class, ['persist' => function () {}, 'flush' => function () {}]),
            Mockery::mock(Mailchimp::class, ['post' => function () {}]),
            $this->mockValidatorFactory(true)
        );
        $listMemberService->addMemberToList(new MailChimpList(), new MailChimpListMember());
    }

    public function testAddMemberToList()
    {
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('toArray')->once()->andReturn([]);
        $collection
            ->shouldReceive('get')
            ->once()
            ->withArgs(function (string $key) {
                return $key === 'unique_email_id';
            })
            ->andReturn('testunique')
        ;

        $listMemberService = new ListMemberService(
            Mockery::mock(EntityManagerInterface::class, ['persist' => function () {}, 'flush' => function () {}]),
            Mockery::mock(Mailchimp::class, ['post' => $collection]),
            $this->mockValidatorFactory(false)
        );

        $list = new MailChimpList();
        $member = new MailChimpListMember(['email_address' => 'test101@test.com']);
        $listMemberService->addMemberToList($list, $member);

        $this->assertSame($list, $member->getList());
        $this->assertSame('testunique', $member->getMailChimpId());
        $this->assertSame('81d66f21a8787aabfd3632faa556728d', $member->getMd5Id());
    }

    public function testUpdateListMemberValidationException()
    {
        $this->expectException(ValidationException::class);

        $listMemberService = new ListMemberService(
            Mockery::mock(EntityManagerInterface::class, ['persist' => function () {}, 'flush' => function () {}]),
            Mockery::mock(Mailchimp::class, ['post' => function () {}]),
            $this->mockValidatorFactory(true)
        );
        $listMemberService->updateListMember(new MailChimpListMember(), []);
    }

    public function testUpdateMemberList()
    {
        $collection = Mockery::mock(Collection::class);
        $collection
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(['last_changed' => '2018-10-30T09:49:50+00:00'])
        ;

        $listMemberService = new ListMemberService(
            Mockery::mock(EntityManagerInterface::class, ['persist' => function () {}, 'flush' => function () {}]),
            Mockery::mock(Mailchimp::class, ['patch' => $collection]),
            $this->mockValidatorFactory(false)
        );

        $member = new MailChimpListMember(['email_address' => 'test101@test.com']);
        $member->setList((new MailChimpList())->setMailChimpId('testlistid'));
        $listMemberService->updateListMember($member, []);

        $this->assertInstanceOf(\DateTimeImmutable::class, $member->getLastChanged());
    }

    private function mockValidatorFactory(bool $responseForFail): MockInterface
    {
        $validatorFactory = Mockery::mock(Factory::class);
        $validator = Mockery::mock(Validator::class);
        $validator
            ->shouldReceive('fails')
            ->once()
            ->andReturn($responseForFail)
        ;

        $validatorFactory
            ->shouldReceive('make')
            ->once()
            ->andReturn($validator)
        ;

        return $validatorFactory;
    }
}
