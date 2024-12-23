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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getLastVisit()
    {
        return $this->lastVisit;
    }

    /**
     * @param \DateTime $lastVisit
     *
     * @return CustomerVisitor
     */
    public function setLastVisit($lastVisit)
    {
        $this->lastVisit = $lastVisit;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     *
     * @return CustomerVisitor
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->sessionId = CustomerVisitorManager::generateSessionId();
    }

    /**
     * @param CustomerUser|null $customerUser
     * @return $this
     */
    public function setCustomerUser(CustomerUser $customerUser = null)
    {
        $this->customerUser = $customerUser;

        return $this;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    #[\Override]
    public function getRoles(): array
    {
        return ['ROLE_FRONTEND_ANONYMOUS'];
    }

    #[\Override]
    public function eraseCredentials()
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
}
