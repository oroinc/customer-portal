<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_CustomerVisitor;
use Oro\Bundle\CustomerBundle\Security\VisitorIdentifierUtil;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Represents guest user with related CustomerUser (which is empty by default)
 * @mixin OroCustomerBundle_Entity_CustomerVisitor
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_customer_visitor')]
#[Index(columns: ['id', 'session_id'], name: 'id_session_id_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config]
class CustomerVisitor implements ExtendEntityInterface, UserInterface
{
    use ExtendEntityTrait;

    protected const string ANONYMOUS_SESSION_ID = 'anonymous';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'last_visit', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $lastVisit;

    #[ORM\Column(name: 'session_id', type: Types::STRING, length: 255, nullable: false)]
    private ?string $sessionId = null;

    #[ORM\OneToOne(targetEntity: CustomerUser::class, cascade: ['persist'])]
    #[ORM\JoinColumn(
        name: 'customer_user_id',
        referencedColumnName: 'id',
        unique: true,
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?CustomerUser $customerUser = null;

    public function __construct()
    {
        $this->lastVisit = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastVisit(): \DateTimeInterface
    {
        return $this->lastVisit;
    }

    public function setLastVisit(\DateTimeInterface $lastVisit): static
    {
        $this->lastVisit = $lastVisit;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->sessionId = CustomerVisitorManager::generateSessionId();
    }

    public function setCustomerUser(?CustomerUser $customerUser = null): static
    {
        $this->customerUser = $customerUser;

        return $this;
    }

    public function getCustomerUser(): ?CustomerUser
    {
        return $this->customerUser;
    }

    #[\Override]
    public function getRoles(): array
    {
        return ['ROLE_FRONTEND_ANONYMOUS'];
    }

    #[\Override]
    public function eraseCredentials(): void
    {
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return VisitorIdentifierUtil::encodeIdentifier($this->getId(), $this->getSessionId());
    }

    public static function isAnonymousSession(string $sessionId): bool
    {
        return $sessionId === self::ANONYMOUS_SESSION_ID;
    }

    public static function createAnonymous(): static
    {
        $visitor = new self();
        $visitor->setSessionId(self::ANONYMOUS_SESSION_ID);

        return $visitor;
    }

    public function isAnonymous(): bool
    {
        return $this->getSessionId() === self::ANONYMOUS_SESSION_ID;
    }
}
