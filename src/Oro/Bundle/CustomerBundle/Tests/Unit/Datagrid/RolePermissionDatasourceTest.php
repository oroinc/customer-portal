<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Datagrid\RolePermissionDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Permission\PermissionManager;
use Oro\Bundle\SecurityBundle\Entity\Permission;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class RolePermissionDatasourceTest extends \PHPUnit\Framework\TestCase
{
    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $translator;

    /** @var RolePrivilegeCategoryProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $categoryProvider;

    /** @var AclRoleHandler|\PHPUnit\Framework\MockObject\MockObject */
    protected $aclRoleHandler;

    /** @var PermissionManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $permissionManager;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $configEntityManager;

    /** @var RoleTranslationPrefixResolver|\PHPUnit\Framework\MockObject\MockObject */
    protected $roleTranslationPrefixResolver;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($value) {
                return $value . '_translated';
            });

        $this->permissionManager = $this->createMock(PermissionManager::class);
        $this->permissionManager->expects($this->any())
            ->method('getPermissionByName')
            ->willReturnCallback(function ($name) {
                $permission = new Permission();
                $permission->setName($name);
                $permission->setLabel($name . 'Label');

                return $permission;
            });

        $this->aclRoleHandler = $this->createMock(AclRoleHandler::class);

        $this->categoryProvider = $this->createMock(RolePrivilegeCategoryProvider::class);
        $this->categoryProvider->expects($this->any())
            ->method('getCategories')
            ->willReturn([]);

        $this->configEntityManager = $this->createMock(ConfigManager::class);

        $this->roleTranslationPrefixResolver = $this->createMock(RoleTranslationPrefixResolver::class);
        $this->roleTranslationPrefixResolver->expects($this->any())
            ->method('getPrefix')
            ->willReturn('prefix.key.');
    }

    public function testGetResults()
    {
        $datasource = $this->getDatasource();
        $identity = 'entity:Oro\Bundle\CustomerBundle\Entity\Customer';

        $results = $this->retrieveResultsFromPermissionsDatasource($datasource, $identity);

        $this->assertCount(1, $results);

        /** @var ResultRecord $record */
        $record = array_shift($results);

        $this->assertInstanceOf(ResultRecord::class, $record);
        $this->assertEquals($identity, $record->getValue('identity'));
        $this->assertNotEmpty($record->getValue('permissions'));
    }

    protected function getDatasource(): RolePermissionDatasource
    {
        return new RolePermissionDatasource(
            $this->translator,
            $this->permissionManager,
            $this->aclRoleHandler,
            $this->categoryProvider,
            $this->configEntityManager,
            $this->roleTranslationPrefixResolver
        );
    }

    /**
     * @param RolePermissionDatasource $datasource
     * @param string                   $identity
     *
     * @return ResultRecordInterface[]
     */
    protected function retrieveResultsFromPermissionsDatasource(
        RolePermissionDatasource $datasource,
        string $identity
    ): array {
        $role = new Role('');

        $datasource->process($this->getDatagrid($role), []);

        $this->aclRoleHandler->expects($this->once())
            ->method('getAllPrivileges')
            ->with($role)
            ->willReturn(
                [
                    'action' => new ArrayCollection(
                        [
                            $this->getAclPrivilege('action:test_action', 'test', new AclPermission('test', 1))
                        ]
                    ),
                    'entity' => new ArrayCollection(
                        [
                            $this->getAclPrivilege(
                                $identity,
                                'TEST',
                                new AclPermission('TEST', AccessLevel::GLOBAL_LEVEL)
                            )
                        ]
                    )
                ]
            );

        return $datasource->getResults();
    }

    protected function getAclPrivilege(string $id, string $name, AclPermission $permission): AclPrivilege
    {
        $identity = new AclPrivilegeIdentity($id, $name);

        $privilege = $this->createMock(AclPrivilege::class);
        $privilege->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
        $privilege->expects($this->any())
            ->method('getPermissions')
            ->willReturn(new ArrayCollection([$permission->getName() => $permission]));
        $privilege->expects($this->any())
            ->method('getFields')
            ->willReturn(new ArrayCollection());

        return $privilege;
    }

    protected function getDatagrid(Role $role): DatagridInterface
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())
            ->method('getParameters')
            ->willReturn(new ParameterBag(['role' => $role]));
        $datagrid->expects($this->once())
            ->method('setDatasource')
            ->with($this->isInstanceOf(RolePermissionDatasource::class));

        return $datagrid;
    }
}
