<?php

namespace Oro\Bundle\CustomerBundle\Acl\Domain;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\SecurityBundle\Acl\Domain\DomainObjectWrapper;
use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategy as InnerStrategy;
use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategyContextInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;

/**
 * Permission granting strategy decorator that allows access to customer user role
 * at VIEW permission is role is public, self managed and has no assigned customer.
 * This is a temporary solution, remove it in BAP-18087.
 */
class PermissionGrantingStrategy implements PermissionGrantingStrategyInterface
{
    /** @var InnerStrategy */
    private $innerStrategy;

    public function __construct(InnerStrategy $innerStrategy)
    {
        $this->innerStrategy = $innerStrategy;
    }

    /**
     * Gets context this strategy is working in
     *
     * @return PermissionGrantingStrategyContextInterface
     */
    public function getContext()
    {
        return $this->innerStrategy->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(AclInterface $acl, array $masks, array $sids, $administrativeMode = false)
    {
        if ($this->isCustomerRoleGranted($masks)) {
            return true;
        }

        return $this->innerStrategy->isGranted($acl, $masks, $sids, $administrativeMode);
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldGranted(AclInterface $acl, $field, array $masks, array $sids, $administrativeMode = false)
    {
        return $this->innerStrategy->isFieldGranted($acl, $field, $masks, $sids, $administrativeMode);
    }

    /**
     * @param array $masks
     *
     * @return bool
     */
    private function isCustomerRoleGranted(array $masks)
    {
        $context = $this->getContext();
        $securityToken = $context->getSecurityToken();

        if (!$securityToken->getUser() instanceof CustomerUser) {
            return false;
        }

        $object = $context->getObject();
        if ($object instanceof DomainObjectWrapper) {
            $object = $object->getDomainObject();
        }

        if (!$object instanceof CustomerUserRole || null !== $object->getCustomer()) {
            return false;
        }

        $organizationId = null;
        if ($securityToken instanceof OrganizationAwareTokenInterface) {
            $organizationId = $securityToken->getOrganization()->getId();
        }

        return $this->isCustomerRoleAccessible($object, $masks, $organizationId);
    }

    /**
     * @param CustomerUserRole $role
     * @param array            $masks
     * @param int|null         $organizationId
     *
     * @return bool
     */
    private function isCustomerRoleAccessible(CustomerUserRole $role, array $masks, ?int $organizationId)
    {
        return
            $role->isSelfManaged()
            && $role->isPublic()
            && 'VIEW' === $this->getPermission($masks)
            && (!$organizationId || $role->getOrganization()->getId() === $organizationId);
    }

    /**
     * @param array $masks
     *
     * @return string|null
     */
    private function getPermission(array $masks)
    {
        if (empty($masks)) {
            return null;
        }

        $permissions = $this->getContext()->getAclExtension()->getPermissions(reset($masks));
        if (empty($permissions)) {
            return null;
        }

        return reset($permissions);
    }
}
