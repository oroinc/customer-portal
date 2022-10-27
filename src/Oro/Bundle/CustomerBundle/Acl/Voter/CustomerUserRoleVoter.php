<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This voter controls access to CustomerUserRole entity
 */
class CustomerUserRoleVoter extends AbstractEntityVoter
{
    const ATTRIBUTE_VIEW = 'VIEW';
    const ATTRIBUTE_EDIT = 'EDIT';
    const ATTRIBUTE_DELETE = 'DELETE';
    const ATTRIBUTE_ASSIGN = 'ASSIGN';
    const ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_UPDATE = 'FRONTEND_CUSTOMER_ROLE_UPDATE';
    const ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_VIEW = 'FRONTEND_CUSTOMER_ROLE_VIEW';
    const ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_DELETE = 'FRONTEND_CUSTOMER_ROLE_DELETE';

    const VIEW = 'view';
    const UPDATE = 'update';

    /** {@inheritDoc} */
    protected $supportedAttributes = [
        self::ATTRIBUTE_VIEW,
        self::ATTRIBUTE_EDIT,
        self::ATTRIBUTE_DELETE,
        self::ATTRIBUTE_ASSIGN,
        self::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_UPDATE,
        self::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_VIEW,
        self::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_DELETE,
    ];

    private AuthorizationCheckerInterface $authorizationChecker;

    private ?CustomerUserRole $object;
    private ?TokenInterface $token;

    public function __construct(DoctrineHelper $doctrineHelper, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($doctrineHelper);
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$object instanceof CustomerUserRole) {
            return self::ACCESS_ABSTAIN;
        }

        $this->object = $object;
        $this->token = $token;
        try {
            return parent::vote($token, $object, $attributes);
        } finally {
            $this->object = null;
            $this->token = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        switch ($attribute) {
            case static::ATTRIBUTE_VIEW:
            case static::ATTRIBUTE_EDIT:
            case static::ATTRIBUTE_ASSIGN:
                return $this->isGrantedCustomerViewPermission();
            case static::ATTRIBUTE_DELETE:
                return $this->getPermissionForDelete();
            case static::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_VIEW:
                return $this->getPermissionForCustomerRole(self::VIEW);
            case static::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_UPDATE:
                return $this->getPermissionForCustomerRole(self::UPDATE);
            case static::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_DELETE:
                return $this->getFrontendPermissionForDelete();
            default:
                return self::ACCESS_ABSTAIN;
        }
    }

    private function isGrantedCustomerViewPermission(): int
    {
        $customer = $this->object->getCustomer();
        if ($customer && !$this->authorizationChecker->isGranted(static::ATTRIBUTE_VIEW, $customer)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }

    private function getPermissionForDelete(): int
    {
        /** @var CustomerUserRoleRepository $repository */
        $repository = $this->doctrineHelper->getEntityRepository(CustomerUserRole::class);
        if ($repository->isDefaultOrGuestForWebsite($this->object)) {
            return self::ACCESS_DENIED;
        }
        if ($repository->hasAssignedUsers($this->object)) {
            return self::ACCESS_DENIED;
        }

        return $this->isGrantedCustomerViewPermission();
    }

    private function getPermissionForCustomerRole(string $type): int
    {
        if (!$this->token->getUser() instanceof CustomerUser) {
            return self::ACCESS_DENIED;
        }

        $isGranted = false;
        if (self::VIEW === $type) {
            $isGranted = $this->isGrantedViewCustomerUserRole();
        } elseif (self::UPDATE === $type) {
            $isGranted = $this->isGrantedUpdateCustomerUserRole();
        }

        return $isGranted ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }

    private function isGrantedUpdateCustomerUserRole(): bool
    {
        if ($this->object->isPredefined()) {
            return true;
        }

        return $this->authorizationChecker->isGranted(BasicPermission::EDIT, $this->object);
    }

    private function isGrantedViewCustomerUserRole(): bool
    {
        if ($this->object->isPredefined()) {
            return true;
        }

        return $this->authorizationChecker->isGranted(BasicPermission::VIEW, $this->object);
    }

    private function getFrontendPermissionForDelete(): int
    {
        if ($this->object->isPredefined()) {
            return self::ACCESS_DENIED;
        }

        return $this->isGrantedDeleteCustomerUserRole($this->object)
            ? self::ACCESS_GRANTED
            : self::ACCESS_DENIED;
    }

    private function isGrantedDeleteCustomerUserRole(CustomerUserRole $object): bool
    {
        return $this->authorizationChecker->isGranted(BasicPermission::DELETE, $object);
    }
}
