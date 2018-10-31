<?php
declare(strict_types=1);

namespace Tests\App\Functional\Http\Controllers\MailChimp;

use Tests\App\TestCases\MailChimp\ListMemberTestCase;

/**
 * @author Cydrick Nonog <cydrick.dev@gmail.com>
 */
class ListMembersControllerTest extends ListMemberTestCase
{
    public function testCreateListMemberListNotFound()
    {
        $this->post('/mailchimp/lists/invalid-list-id/members');

        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpList [invalid-list-id] not found'), $content['message']);
    }

    public function testCreateListMemberValidationFailed()
    {
        $list = $this->createList();
        $this->post(sprintf('/mailchimp/lists/%s/members', $list->getId()));

        $content = \json_decode($this->response->getContent(), true);


        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(422);
        self::assertArrayHasKey('errors', $content);
        self::assertEquals('Invalid data given', $content['message']);

        foreach (\array_keys(static::$listMemberData) as $key) {
            if (\in_array($key, static::$notRequired, true)) {
                continue;
            }

            self::assertArrayHasKey($key, $content['errors']);
        }
    }

    public function testCreateListMemberSuccessfully()
    {
        $list = $this->createList();

        $this->post(sprintf('mailchimp/lists/%s/members', $list->getId()), $this->getMemberData());
        $content = \json_decode($this->response->getContent(), true);
        self::assertResponseOk();
        self::assertArrayHasKey('mail_chimp_id', $content);
        self::assertNotNull($content['mail_chimp_id']);

        $this->createdListMemberEmailIds[$list->getMailChimpId()][] = $content['md5_id'];
    }

    public function testUpdateListMemberListNotFound()
    {
        $this->put('/mailchimp/lists/invalid-list-id/members/invalid-member');
        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpList [invalid-list-id] not found'), $content['message']);
    }

    public function testUpdateListMemberNotFound()
    {
        $list = $this->createList();

        $this->put(sprintf('/mailchimp/lists/%s/members/invalid-member', $list->getId()));

        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpListMember [invalid-member] not found'), $content['message']);
    }

    public function testUpdateListMemberValidationFailed()
    {
        $list = $this->createList();

        $this->post(sprintf('mailchimp/lists/%s/members', $list->getId()), $this->getMemberData());
        $memberContent = \json_decode($this->response->getContent(), true);
        $this->createdListMemberEmailIds[$list->getMailChimpId()][] = $memberContent['md5_id'];

        $this->put(sprintf('/mailchimp/lists/%s/members/%s', $list->getId(), $memberContent['list_member_id']), ['email_address' => 'wrongemail']);
        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(422);
        self::assertArrayHasKey('errors', $content);
        self::assertEquals('Invalid data given', $content['message']);
    }

    public function testUpdateListMemberSuccessfully()
    {
        $list = $this->createList();
        $memberData = $this->getMemberData();

        $this->post(sprintf('mailchimp/lists/%s/members', $list->getId()), $memberData);

        $memberContent = \json_decode($this->response->getContent(), true);
        $this->createdListMemberEmailIds[$list->getMailChimpId()][] = $memberContent['md5_id'];

        $this->put(sprintf('/mailchimp/lists/%s/members/%s', $list->getId(), $memberContent['list_member_id']), ['vip' => false]);
        $content = \json_decode($this->response->getContent(), true);

        self::assertResponseOk();
        self::assertArrayHasKey('mail_chimp_id', $content);
        self::assertNotNull($content['mail_chimp_id']);
    }

    public function testRemoveListMemberListNotFound()
    {
        $this->delete('/mailchimp/lists/invalid-list-id/members/invalid-member');
        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpList [invalid-list-id] not found'), $content['message']);
    }

    public function testRemoveListMemberNotFound()
    {
        $list = $this->createList();

        $this->delete(sprintf('/mailchimp/lists/%s/members/invalid-member', $list->getId()));

        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpListMember [invalid-member] not found'), $content['message']);
    }

    public function testRemoveListMemberSuccessfully()
    {
        $list = $this->createList();

        $this->post(sprintf('mailchimp/lists/%s/members', $list->getId()), $this->getMemberData());
        $memberContent = \json_decode($this->response->getContent(), true);
        $this->createdListMemberEmailIds[$list->getMailChimpId()][] = $memberContent['md5_id'];

        $this->delete(sprintf('/mailchimp/lists/%s/members/%s', $list->getId(), $memberContent['list_member_id']));
        self::assertResponseOk();
    }

    public function testShowListListNotFound()
    {
        $this->get('/mailchimp/lists/invalid-list-id/members');
        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpList [invalid-list-id] not found'), $content['message']);
    }

    public function testShowListSuccessfully()
    {
        $list = $this->createList();
        $this->get(sprintf('/mailchimp/lists/%s/members', $list->getId()));
        $content = \json_decode($this->response->getContent(), true);

        self::assertResponseOk();
        self::assertArrayHasKey('offset', $content);
        self::assertArrayHasKey('count', $content);
        self::assertArrayHasKey('members', $content);
    }

    public function testShowListNotFound()
    {
        $this->get('/mailchimp/lists/invalid-list-id/members/invalid-member');
        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpList [invalid-list-id] not found'), $content['message']);
    }

    public function testShowListMemberNotFound()
    {
        $list = $this->createList();

        $this->get(sprintf('/mailchimp/lists/%s/members/invalid-member', $list->getId()));

        $content = \json_decode($this->response->getContent(), true);

        self::assertArrayHasKey('message', $content);
        self::assertResponseStatus(404);
        self::assertEquals(sprintf('MailChimpListMember [invalid-member] not found'), $content['message']);
    }

    public function testShowSuccessfully()
    {
        $list = $this->createList();
        $memberData = $this->getMemberData();

        $this->post(sprintf('mailchimp/lists/%s/members', $list->getId()), $memberData);

        $memberContent = \json_decode($this->response->getContent(), true);
        $this->createdListMemberEmailIds[$list->getMailChimpId()][] = $memberContent['md5_id'];

        $this->get(sprintf('/mailchimp/lists/%s/members/%s', $list->getId(), $memberContent['list_member_id']));
        $content = \json_decode($this->response->getContent(), true);

        self::assertResponseOk();
        self::assertArrayHasKey('mail_chimp_id', $content);
        self::assertNotNull($content['mail_chimp_id']);
    }
}
