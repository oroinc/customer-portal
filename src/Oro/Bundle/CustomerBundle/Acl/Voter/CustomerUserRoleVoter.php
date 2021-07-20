<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRoleRepository;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    /**
     * @var array
     */
    protected $supportedAttributes = [
        self::ATTRIBUTE_VIEW,
        self::ATTRIBUTE_EDIT,
        self::ATTRIBUTE_DELETE,
        self::ATTRIBUTE_ASSIGN,
        self::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_UPDATE,
        self::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_VIEW,
        self::ATTRIBUTE_FRONTEND_CUSTOMER_ROLE_DELETE,
    ];

    /**
     * @var CustomerUserRole
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $this->object = $object;

        return parent::vote($token, $object, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if (!$this->object instanceof CustomerUserRole) {
            return self::ACCESS_ABSTAIN;
        }

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

    /**
     * @return int
     */
    protected function isGrantedCustomerViewPermission()
    {
        $customer = $this->object->getCustomer();
        if ($customer && !$this->getAuthorizationChecker()->isGranted(static::ATTRIBUTE_VIEW, $customer)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_GRANTED;
    }

    /**
     * @return int
     */
    protected function getPermissionForDelete()
    {
        /** @var CustomerUserRoleRepository $repository */
        $repository = $this->doctrineHelper->getEntityRepository('OroCustomerBundle:CustomerUserRole');
        if ($repository->isDefaultOrGuestForWebsite($this->object)) {
            return self::ACCESS_DENIED;
        }
        if ($repository->hasAssignedUsers($this->object)) {
            return self::ACCESS_DENIED;
        }

        return $this->isGrantedCustomerViewPermission();
    }

    /**
     * @param string $type
     * @return int
     */
    protected function getPermissionForCustomerRole($type)
    {
        if (!$this->getLoggedUser() instanceof CustomerUser) {
            return self::ACCESS_DENIED;
        }

        $isGranted = false;

        switch ($type) {
            case self::VIEW:
                $isGranted = $this->isGrantedViewCustomerUserRole();
                break;
            case self::UPDATE:
                $isGranted = $this->isGrantedUpdateCustomerUserRole();
                break;
        }

        return $isGranted ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    protected function getAuthorizationChecker()
    {
        return $this->container->get('security.authorization_checker');
    }

    /**
     * @return TokenAccessorInterface
     */
    protected function getTokenAccessor()
    {
        return $this->container->get('oro_security.token_accessor');
    }

    /**
     * @return AbstractUser|null
     */
    protected function getLoggedUser()
    {
        return $this->getTokenAccessor()->getUser();
    }

    /**
     * @return boolean
     */
    protected function isGrantedUpdateCustomerUserRole()
    {
        if ($this->object->isPredefined()) {
            return true;
        }

        return $this->getAuthorizationChecker()->isGranted(BasicPermission::EDIT, $this->object);
    }

    /**
     * @return boolean
     */
    protected function isGrantedViewCustomerUserRole()
    {
        if ($this->object->isPredefined()) {
            return true;
        }

        return $this->getAuthorizationChecker()->isGranted(BasicPermission::VIEW, $this->object);
    }

    /**
     * @return bool
     * @return int
     */
    protected function getFrontendPermissionForDelete()
    {
        if ($this->object->isPredefined()) {
            return self::ACCESS_DENIED;
        }

        return $this->isGrantedDeleteCustomerUserRole($this->object) ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
    }

    /**
     * @param $object
     * @return bool
     */
    protected function isGrantedDeleteCustomerUserRole($object)
    {
        return $this->getAuthorizationChecker()->isGranted(BasicPermission::DELETE, $object);
    }
}
