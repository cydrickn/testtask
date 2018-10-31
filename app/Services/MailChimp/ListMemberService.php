<?php
declare(strict_types=1);

namespace App\Services\MailChimp;

use App\Database\Entities\MailChimp\MailChimpList;
use App\Database\Entities\MailChimp\MailChimpListMember;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mailchimp\Mailchimp;

/**
 * Service for List Member
 *
 * @author Cydrick Nonog <cydrick.dev@gmail.com>
 */
class ListMemberService
{
    /**
     * Mailchip
     *
     * @var Mailchimp
     */
    private $mailChimp;

    /**
     * Entity manager
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Validator Factory
     *
     * @var \Illuminate\Validation\Factory
     */
    private $validatorFactory;

    /**
     * ListMemberService Constructor
     *
     * @param EntityManagerInterface $entityManager
     * @param Mailchimp $mailChimp
     * @param \Illuminate\Validation\Factory $validatorFactory
     */
    public function __construct(EntityManagerInterface $entityManager, Mailchimp $mailChimp, Factory $validatorFactory)
    {
        $this->entityManager = $entityManager;
        $this->mailChimp = $mailChimp;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * Add Member to MailChimp List
     *
     * @param MailChimpList $list
     * @param MailChimpListMember $member
     *
     * @throws ValidationException
     */
    public function addMemberToList(MailChimpList $list, MailChimpListMember $member): void
    {
        $validator = $this->validatorFactory->make($member->toMailChimpArray(), $member->getValidationRules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->saveEntity($member);

        $response = $this->mailChimp->post(
            sprintf('lists/%s/members', $list->getMailChimpId()),
            $member->toMailChimpArray()
        );

        $member
            ->fill($response->toArray())
            ->setList($list)
            ->setMailChipId($response->get('unique_email_id'));

        $this->saveEntity($member);
    }

    /**
     * Update member list
     *
     * @param MailChimpListMember $member
     *
     * @throws ValidationException
     */
    public function updateListMember(MailChimpListMember $member, array $updatedData): void
    {
        $member->fill($updatedData);
        $validator = $this->validatorFactory->make($member->toMailChimpArray(), $member->getValidationRules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->saveEntity($member);
        $response = $this->mailChimp->patch(
            sprintf('/lists/%s/members/%s', $member->getList()->getMailChimpId(), $member->getMd5Id()),
            $updatedData
        );
        $member->fill($response->toArray());

        $this->saveEntity($member);
    }

    /**
     * Remove member list
     *
     * @param MailChimpListMember $member
     */
    public function removeListMember(MailChimpListMember $member): void
    {
        $this->removeEntity($member);
        $this->mailChimp->delete(\sprintf('lists/%s/members/%s', $member->getList()->getMailChimpId(), $member->getMd5Id()));
    }

    /**
     * Retrieve members from list
     *
     * @param MailChimpList $list
     * @param int $offset
     * @param int $count
     *
     * @return array
     */
    public function retrieveMembersFromList(MailChimpList $list, int $offset = 0, int $count = 10): array
    {
        $memberCollection = $list->getMembers();

        $criteria = Criteria::create()
            ->setFirstResult($offset)
            ->setMaxResults($count)
        ;

        return  $memberCollection->matching($criteria)->toArray();
    }

    /**
     * Save entity
     *
     * @param MailChimpListMember $member
     */
    private function saveEntity(MailChimpListMember $member): void
    {
        $this->entityManager->persist($member);
        $this->entityManager->flush();
    }

    /**
     * Remove entity
     *
     * @param MailChimpListMember $member
     */
    private function removeEntity(MailChimpListMember $member): void
    {
        $this->entityManager->remove($member);
        $this->entityManager->flush();
    }
}
