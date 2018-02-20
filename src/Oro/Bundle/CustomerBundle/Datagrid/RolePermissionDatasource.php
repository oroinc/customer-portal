<?php

namespace Oro\Bundle\CustomerBundle\Datagrid;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Acl\Permission\PermissionManager;
use Oro\Bundle\UserBundle\Datagrid\RolePermissionDatasource as BaseRolePermissionDatasource;
use Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Symfony\Component\Translation\TranslatorInterface;

class RolePermissionDatasource extends BaseRolePermissionDatasource
{
    /** @var RoleTranslationPrefixResolver */
    protected $roleTranslationPrefixResolver;

    /**
     * @param TranslatorInterface $translator
     * @param PermissionManager $permissionManager
     * @param AclRoleHandler $aclRoleHandler
     * @param RolePrivilegeCategoryProvider $categoryProvider
     * @param ConfigManager $configEntityManager
     * @param RoleTranslationPrefixResolver $roleTranslationPrefixResolver
     */
    public function __construct(
        TranslatorInterface $translator,
        PermissionManager $permissionManager,
        AclRoleHandler $aclRoleHandler,
        RolePrivilegeCategoryProvider $categoryProvider,
        ConfigManager $configEntityManager,
        RoleTranslationPrefixResolver $roleTranslationPrefixResolver
    ) {
        parent::__construct($translator, $permissionManager, $aclRoleHandler, $categoryProvider, $configEntityManager);

        $this->roleTranslationPrefixResolver = $roleTranslationPrefixResolver;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoleTranslationPrefix()
    {
        return $this->roleTranslationPrefixResolver->getPrefix();
    }
}
