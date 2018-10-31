<?php
declare(strict_types=1);

namespace Tests\App\TestCases\MailChimp;

use App\Database\Entities\MailChimp\MailChimpList;
use Mailchimp\Mailchimp;
use Tests\App\TestCases\WithDatabaseTestCase;

/**
 * @author Cydrick Nonog <cydrick.dev@gmail.com>
 */
abstract class ListMemberTestCase extends WithDatabaseTestCase
{
    protected $lists = [];


    /**
     * @var array
     */
    protected static $listData = [
        'name' => 'New list',
        'permission_reminder' => 'You signed up for updates on Greeks economy.',
        'email_type_option' => false,
        'contact' => [
            'company' => 'Doe Ltd.',
            'address1' => 'DoeStreet 1',
            'address2' => '',
            'city' => 'Doesy',
            'state' => 'Doedoe',
            'zip' => '1672-12',
            'country' => 'US',
            'phone' => '55533344412'
        ],
        'campaign_defaults' => [
            'from_name' => 'John Doe',
            'from_email' => 'john@doe.com',
            'subject' => 'My new campaign!',
            'language' => 'US'
        ],
        'visibility' => 'prv',
        'use_archive_bar' => false,
        'notify_on_subscribe' => 'notify@loyaltycorp.com.au',
        'notify_on_unsubscribe' => 'notify@loyaltycorp.com.au'
    ];


    /**
     * @var array
     */
    protected $createdListMemberEmailIds = [];

    /**
     * @var array
     */
    protected static $listMemberData = [
        'email_address' => 'test102@gmail.com',
        'email_type' => 'text',
        'status' => 'pending',
        'merge_fields' => [
            'FNAME' => 'Cydrick',
            'LNAME' => 'Nonog',
            'ADDRESS' => '',
            'PHONE' => ''
        ],
        'language' => 'en',
        'vip' => true,
        'location' => [
            'latitude' => 16.402332,
            'longitude' => 120.596008
        ],
        'marketing_permissions' => [],
        'ip_signup' => '192.168.0.1',
        'timestamp_signup' => '2018-10-30 10:00:01',
        'ip_opt' => '172.132.10.1',
        'timestamp_opt' => '2018-10-30 10:00:01',
        'tags' => []
    ];

    /**
     * @var array
     */
    protected static $notRequired = [
        'email_type',
        'merge_fields',
        'interests',
        'language',
        'vip',
        'location',
        'marketing_permissions',
        'ip_signup',
        'timestamp_signup',
        'ip_opt',
        'timestamp_opt',
        'tags'
    ];

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Delete members
     *
     * @return void
     */
    public function tearDown(): void
    {
        $mailChimp = $this->app->make(Mailchimp::class);
        foreach ($this->lists as $list) {
            $mailChimp->delete(\sprintf('lists/%s', $list->getMailChimpId()));
            $this->entityManager->remove($list);
        }
        $this->entityManager->flush();

        parent::tearDown();
    }

    protected function createList(): MailChimpList
    {
        // Create list in mailchimp
        $mailChimp = $this->app->make(Mailchimp::class);
        $response = $mailChimp->post('lists', self::$listData);
        $this->createdListMemberEmailIds[$response['id']] = [];

        $list = new MailChimpList(self::$listData);
        $list->setMailChimpId($response['id']);
        $this->entityManager->persist($list);
        $this->entityManager->flush();

        $this->lists[] = $list;

        return $list;
    }

    protected function getMemberData(): array
    {
        $memberData = self::$listMemberData;
        $memberData['email_address'] = 'test' . date('YmdHis') . '@test.com';

        return $memberData;
    }
}