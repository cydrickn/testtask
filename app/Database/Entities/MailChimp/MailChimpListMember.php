<?php
declare(strict_types=1);

namespace App\Database\Entities\MailChimp;

use Doctrine\ORM\Mapping as ORM;
use EoneoPay\Utils\Str;

/**
 * @ORM\Entity()
 * @ORM\Table(name="mail_chimp_list_member",indexes={@ORM\Index(name="mail_chimp_id", columns={"mail_chimp_id"})})
 */
class MailChimpListMember extends MailChimpEntity
{
    /**
     * @ORM\Column(name="email_address", type="string")
     *
     * @var string
     */
    private $emailAddress;

    /**
     * @ORM\Column(name="email_client", type="string", nullable=true)
     *
     * @var string
     */
    private $emailClient;

    /**
     * @ORM\Column(name="email_type", type="string", nullable=true)
     *
     * @var string
     */
    private $emailType;

    /**
     * @ORM\Column(name="interests", type="array")
     *
     * @var array
     */
    private $interests;

    /**
     * @ORM\Column(name="ip_signup", type="string", nullable=true)
     *
     * @var string
     */
    private $ipSignup;

    /**
     * @ORM\Column(name="ip_opt", type="string", nullable=true)
     *
     * @var string
     */
    private $ipOpt;

    /**
     * @ORM\Column(name="language", type="string", nullable=true)
     *
     * @var string
     */
    private $language;

    /**
     * @ORM\Column(name="last_changed", type="datetime_immutable", nullable=true)
     *
     * @var \DateTimeImmutable
     */
    private $lastChanged;

    /**
     * @ORM\Column(name="last_note", type="array")
     *
     * @var array
     */
    private $lastNote;

    /**
     * @ORM\ManyToOne(targetEntity="MailChimpList", inversedBy="members")
     * @ORM\JoinColumn(name="list_id", referencedColumnName="id")
     *
     * @var string
     */
    private $list;

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string
     */
    private $listMemberId;

    /**
     * @ORM\Column(name="location", type="json")
     *
     * @var array
     */
    private $location = [];

    /**
     * @ORM\Column(name="mail_chimp_id", type="string", nullable=true)
     *
     * @var string
     */
    private $mailChimpId;

    /**
     * @ORM\Column(name="marketing_permissions", type="json")
     *
     * @var array
     */
    private $marketingPermissions = [];

    /**
     * @ORM\Column(name="member_rating", type="integer", nullable=true)
     *
     * @var int
     */
    private $memberRating;

    /**
     * @ORM\Column(name="merge_fields", type="json")
     *
     * @var array
     */
    private $mergeFields = [];

    /**
     * @ORM\Column(name="stats", type="json", nullable=true)
     *
     * @var array
     */
    private $stats;

    /**
     * @ORM\Column(name="status", type="string")
     *
     * @var string
     */
    private $status;

    /**
     * @ORM\Column(name="tags", type="json")
     *
     * @var array
     */
    private $tags = [];

    /**
     * @ORM\Column(name="timestamp_opt", type="datetime_immutable", nullable=true)
     *
     * @var \DateTimeImmutable
     */
    private $timestampOpt;

    /**
     * @ORM\Column(name="timestamp_signup", type="datetime_immutable", nullable=true)
     *
     * @var \DateTimeImmutable
     */
    private $timestampSignup;

    /**
     * @ORM\Column(name="unsubscribe_reason", type="string", nullable=true)
     *
     * @var string
     */
    private $unsubscribeReason;

    /**
     * @ORM\Column(name="vip", type="boolean", options={"default" : false})
     *
     * @var bool
     */
    private $vip = false;

    /**
     * Update entity properties with given data.
     *
     * @param array $data
     *
     * @return \App\Database\Entities\Entity
     */
    public function fill(array $data): \App\Database\Entities\Entity
    {
        $str = new Str();

        foreach ($data ?? [] as $property => $value) {
            $setter = \sprintf('set%s', $str->studly($property));

            // If setter for current property exist then call it to set value
            if (\method_exists($this, $setter)) {
                if (in_array($property, ['timestamp_signup', 'timestamp_opt', 'last_changed'])) {
                    $dateTimeValue = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, $value);
                    if ($dateTimeValue instanceof \DateTimeImmutable) {
                        $this->$setter($dateTimeValue);
                    }
                } elseif (is_object ($value)) {
                    $this->$setter((array) $value);
                } else {
                    $this->$setter($value);
                }
            }
        }

        return $this;
    }

    /**
     * Get list member id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->listMemberId;
    }

    public function getLastChanged(): ?\DateTimeImmutable
    {
        return $this->lastChanged;
    }

    public function getList(): ?MailChimpList
    {
        return $this->list;
    }

    /**
     * Get mail chimp unique id
     *
     * @return string|null
     */
    public function getMailChimpId(): ?string
    {
        return $this->mailChimpId;
    }

    /**
     * Get MD5 hash of the lowercase version of the list member's email address.
     *
     * @return string|null
     */
    public function getMd5Id(): ?string
    {
        if ($this->emailAddress !== null) {
            return md5(strtolower($this->emailAddress));
        }

        return null;
    }

    /**
     * Get validation rules for mailchimp list member
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'email_address' => 'required|email',
            'email_type' => 'nullable|string|in:html,text',
            'status' => 'required|string|in:subscribed,unsubscribed,cleaned,pending',
            'merge_fields' => 'nullable|array',
            'interests' => 'array',
            'language' => 'nullable|string',
            'vip' => 'boolean',
            'location' => 'array',
            'location.latitude' => 'numeric',
            'location.longitude' => 'numeric',
            'marketing_permissions' => 'array',
            'marketing_permissions.*.marketing_permission_id' => 'string',
            'marketing_permissions.*.enabled' => 'boolean',
            'ip_signup' => 'nullable|ip',
            'timestamp_signup' => 'nullable',
            'ip_opt' => 'nullable|ip',
            'timestamp_opt' => 'nullable',
            'tags' => 'array'
        ];
    }

    /**
     * Set email address
     *
     * @param string $emailAddress
     *
     * @return MailChimpListMember
     */
    public function setEmailAddress(string $emailAddress): MailChimpListMember
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Set email client
     *
     * @param string $emailClient
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setEmailClient(string $emailClient): MailChimpListMember
    {
        $this->emailClient = $emailClient;

        return $this;
    }

    /**
     * Set email type
     *
     * @param string $emailType
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setEmailType(string $emailType): MailChimpListMember
    {
        $this->emailType = $emailType;

        return $this;
    }

    /**
     * Set interests
     *
     * @param array $interests
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setInterests(array $interests): MailChimpListMember
    {
        $this->interests = $interests;

        return $this;
    }

    /**
     * Set ip opt
     *
     * @param string $ipOpt
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setIpOpt(string $ipOpt): MailChimpListMember
    {
        $this->ipOpt = $ipOpt;

        return $this;
    }

    /**
     * Set ip sign up
     *
     * @param string $ipSignup
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setIpSignup(string $ipSignup): MailChimpListMember
    {
        $this->ipSignup = $ipSignup;

        return $this;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setLanguage(string $language): MailChimpListMember
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Set last changed
     *
     * @param \DateTimeInterface $lastChanged
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setLastChanged(\DateTimeInterface $lastChanged): MailChimpListMember
    {
        if (!($lastChanged instanceof \DateTimeImmutable)) {
            $this->lastChanged = \DateTimeImmutable::createFromMutable($lastChanged);
        } else {
            $this->lastChanged = $lastChanged;
        }

        return $this;
    }

    /**
     * Set last note
     *
     * @param array $lastNote
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setLastNote(array $lastNote): MailChimpListMember
    {
        $this->lastNote = $lastNote;

        return $this;
    }

    /**
     * Set list
     *
     * @param MailChimpList $list
     *
     * @return MailChimpListMember
     */
    public function setList(MailChimpList $list): MailChimpListMember
    {
        if ($this->list !== $list) {
            $this->list = $list;
            $this->list->addMember($this);
        }

        return $this;
    }

    /**
     * Set location
     *
     * @param array $location
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setLocation(array $location): MailChimpListMember
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Set mail chip id
     *
     * @param string $mailChipId
     *
     * @return MailChimpListMember
     */
    public function setMailChipId(string $mailChipId): MailChimpListMember
    {
        $this->mailChimpId = $mailChipId;

        return $this;
    }

    /**
     * Set marketing permissions
     *
     * @param array $marketingPermissions
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setMarketingPermissions(array $marketingPermissions): MailChimpListMember
    {
        $this->marketingPermissions = $marketingPermissions;

        return $this;
    }

    /**
     * Set member rating
     *
     * @param int $memberRating
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setMemberRating(int $memberRating): MailChimpListMember
    {
        $this->memberRating = $memberRating;

        return $this;
    }

    /**
     * Set merge fields
     *
     * @param array $mergeFields
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setMergeFields(array $mergeFields): MailChimpListMember
    {
        $this->mergeFields = $mergeFields;

        return $this;
    }

    /**
     *
     * @param array $stats
     *
     * @return MailChimpListMember
     */
    public function setStats(array $stats): MailChimpListMember
    {
        $this->stats = $stats;

        return $this;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setStatus(string $status): MailChimpListMember
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set tags
     *
     * @param array $tags
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setTags(array $tags): MailChimpListMember
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Set timestamp opt
     *
     * @param \DateTimeInterface $timestampOpt
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setTimestampOpt(\DateTimeInterface $timestampOpt): MailChimpListMember
    {
        if (!($timestampOpt instanceof \DateTimeImmutable)) {
            $this->timestampOpt = \DateTimeImmutable::createFromMutable($timestampOpt);
        } else {
            $this->timestampOpt = $timestampOpt;
        }

        return $this;
    }

    /**
     * Set timestamp signup
     *
     * @param \DateTimeInterface $timestapSignup
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setTimestampSignup(\DateTimeInterface $timestapSignup): MailChimpListMember
    {
        if (!($timestapSignup instanceof \DateTimeImmutable)) {
            $this->timestampSignup = \DateTimeImmutable::createFromMutable($timestapSignup);
        } else {
            $this->timestampSignup = $timestapSignup;
        }

        return $this;
    }

    /**
     * Set unsubscribe reason
     *
     * @param string $unsubsribeReason
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setUnsubscribeReason(string $unsubsribeReason): MailChimpListMember
    {
        $this->unsubscribeReason = $unsubsribeReason;

        return $this;
    }

    /**
     * Set vip
     *
     * @param bool $vip
     *
     * @return \App\Database\Entities\MailChimp\MailChimpListMember
     */
    public function setVip(bool $vip): MailChimpListMember
    {
        $this->vip = $vip;

        return $this;
    }

    /**
     * Get array representation of entity.
     *
     * @return array
     */
    public function toArray(): array
    {
        $str = new Str();
        $array = ['md5_id' => $this->getMd5Id()];

        foreach (\get_object_vars($this) as $property => $value) {
            if ($value instanceof MailChimpList) {
                $array[$str->snake($property)] = $value->toArray();
            } elseif ($value instanceof \DateTimeInterface) {
                $array[$str->snake($property)] = $value->format(\DateTimeImmutable::ISO8601);
            } else {
                $array[$str->snake($property)] = $value;
            }
        }

        return $array;
    }

    public function toMailChimpArray(): array
    {
        $toMailChimp = parent::toMailChimpArray();
        unset($toMailChimp['md5_id']);

        return $toMailChimp;
    }
}
