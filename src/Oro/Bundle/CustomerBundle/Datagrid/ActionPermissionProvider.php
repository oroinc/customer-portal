<?php

namespace Oro\Bundle\CustomerBundle\Datagrid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Provides action permissions for customer user records in datagrids.
 *
 * This provider determines which actions (enable, disable, view, update, delete) are available
 * for customer user records based on the current user's identity and the record's enabled status.
 * It also provides permissions for customer user role operations.
 */
class ActionPermissionProvider
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param ResultRecordInterface $record
     *
     * @return array
     */
    public function getUserPermissions(ResultRecordInterface $record)
    {
        $disabled = $enabled = $record->getValue('enabled');
        $user = $this->tokenAccessor->getUser();
        $delete = true;
        if ($user instanceof CustomerUser) {
            $isCurrentUser = $user->getId() == $record->getValue('id');
            $disabled = $isCurrentUser ? false : $enabled;
            $delete = !$isCurrentUser;
        }

        return [
            'enable' => !$enabled,
            'disable' => $disabled,
            'view' => true,
            'update' => true,
            'delete' => $delete
        ];
    }

    /**
     * @param ResultRecordInterface $record
     *
     * @return array
     */
    public function getCustomerUserRolePermission(ResultRecordInterface $record)
    {
        $isGranted = true;
        if ($record->getValue('isRolePredefined')) {
            $isGranted = $this->authorizationChecker->isGranted('oro_customer_frontend_customer_user_role_create');
        }

        return [
            'view' => true,
            'update' => $isGranted
        ];
    }
}
