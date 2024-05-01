<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;

/**
 * The entity that stores login attempts of customer users.
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_customer_user_login')]
#[ORM\Index(columns: ['attempt_at'], name: 'oro_cuser_log_att_at_idx')]
#[Config(
    defaultValues: [
        'security' => [
            'type' => 'ACL',
            'group_name' => '',
            'category' => 'account_management',
            'permissions' => 'VIEW'
        ]
    ]
)]
class CustomerUserLoginAttempt
{
    #[ORM\Column(name: 'id', type: Types::GUID)]
    #[ORM\Id]
    private ?string $id = null;

    #[ORM\Column(name: 'attempt_at', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTime $attemptAt = null;

    #[ORM\Column(name: 'success', type: Types::BOOLEAN, nullable: false)]
    private ?bool $success = null;

    #[ORM\Column(name: 'source', type: Types::INTEGER, nullable: false)]
    private ?int $source = null;

    #[ORM\Column(name: 'username', type: Types::STRING, length: 255, nullable: true)]
    private ?string $username;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?CustomerUser $user;

    #[ORM\Column(name: 'ip', type: Types::STRING, length: 255, nullable: true)]
    private ?string $ip;

    #[ORM\Column(name: 'user_agent', type: Types::TEXT, nullable: true, options: ['default' => ''])]
    private ?string $userAgent;

    #[ORM\Column(name: 'context', type: Types::JSON, nullable: false)]
    private ?array $context = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getAttemptAt(): \DateTime
    {
        return $this->attemptAt;
    }

    public function setAttemptAt(\DateTime $attemptAt): void
    {
        $this->attemptAt = $attemptAt;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getSource(): ?int
    {
        return $this->source;
    }

    public function setSource(int $source): void
    {
        $this->source = $source;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUser(): ?CustomerUser
    {
        return $this->user;
    }

    public function setUser(CustomerUser $user): void
    {
        $this->user = $user;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }
}
