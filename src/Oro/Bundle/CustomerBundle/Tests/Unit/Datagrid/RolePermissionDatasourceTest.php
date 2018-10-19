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
use Symfony\Component\Translation\TranslatorInterface;

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

    protected function setUp()
    {
        $this->translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(
                function ($value) {
                    return $value . '_translated';
                }
            );

        $this->permissionManager = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Permission\PermissionManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->permissionManager->expects($this->any())
            ->method('getPermissionByName')
            ->willReturnCallback(
                function ($name) {
                    $permission = new Permission();
                    $permission->setName($name);
                    $permission->setLabel($name . 'Label');

                    return $permission;
                }
            );

        $this->aclRoleHandler = $this->getMockBuilder('Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryProvider = $this->getMockBuilder('Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryProvider->expects($this->any())->method('getPermissionCategories')->willReturn([]);

        $this->configEntityManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->roleTranslationPrefixResolver = $this
            ->getMockBuilder('Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $this->roleTranslationPrefixResolver->expects($this->any())->method('getPrefix')->willReturn('prefix.key.');
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

    /**
     * @return RolePermissionDatasource
     */
    protected function getDatasource()
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
     * @param string $identity
     * @return array|ResultRecordInterface[]
     */
    protected function retrieveResultsFromPermissionsDatasource(RolePermissionDatasource $datasource, $identity)
    {
        $role = new Role();
        
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

    /**
     * @param string $id
     * @param string $name
     * @param AclPermission $permission
     * @return AclPrivilege|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getAclPrivilege($id, $name, AclPermission $permission)
    {
        $identity = new AclPrivilegeIdentity($id, $name);
        
        /** @var AclPrivilege|\PHPUnit\Framework\MockObject\MockObject $privilege */
        $privilege = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Model\AclPrivilege')
            ->disableOriginalConstructor()
            ->getMock();
        $privilege->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
        $privilege->expects($this->any())
            ->method('getPermissions')
            ->willReturn(
                new ArrayCollection(
                    [
                        $permission->getName() => $permission
                    ]
                )
            );
        $privilege->expects($this->any())->method('getFields')->willReturn(new ArrayCollection());

        return $privilege;
    }

    /**
     * @param Role $role
     * @return DatagridInterface
     */
    protected function getDatagrid(Role $role)
    {
        /** @var DatagridInterface|\PHPUnit\Framework\MockObject\MockObject $datagrid */
        $datagrid = $this->createMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');
        $datagrid->expects($this->once())
            ->method('getParameters')
            ->willReturn(new ParameterBag(['role' => $role]));
        $datagrid->expects($this->once())
            ->method('setDatasource')
            ->with($this->isInstanceOf(RolePermissionDatasource::class));

        return $datagrid;
    }
}
