<?php

namespace Oro\Bundle\CustomerBundle\Datagrid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
