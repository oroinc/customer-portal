<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Datagrid\WorkflowPermissionDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\SecurityBundle\Entity\Permission;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Oro\Bundle\UserBundle\Entity\Role;

class WorkflowPermissionDatasourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $permissionManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $aclRoleHandler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $categoryProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $configEntityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $roleTranslationPrefixResolver;

    /** @var WorkflowPermissionDatasource */
    protected $datasource;

    protected function setUp()
    {
        $this->translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $this->permissionManager = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Permission\PermissionManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->aclRoleHandler = $this->getMockBuilder('Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler')
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryProvider = $this->getMockBuilder('Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configEntityManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->roleTranslationPrefixResolver = $this
            ->getMockBuilder('Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->roleTranslationPrefixResolver->expects($this->any())
            ->method('getPrefix')
            ->willReturn('commerce_role_prefix.');

        $this->datasource = new WorkflowPermissionDatasource(
            $this->translator,
            $this->permissionManager,
            $this->aclRoleHandler,
            $this->categoryProvider,
            $this->configEntityManager,
            $this->roleTranslationPrefixResolver
        );
    }

    public function testGetResults()
    {
        $role = new Role();
        $parameters = new ParameterBag();
        $parameters->add(['role' => $role]);
        $datagridConfig = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $grid = new Datagrid('test', $datagridConfig, $parameters);

        $this->datasource->process($grid, []);

        $privilege1 = new AclPrivilege();
        $privilege1->setIdentity(new AclPrivilegeIdentity('workflow:workflow1', 'workflow 1'));
        $privilege1->addPermission(new AclPermission('VIEW_WORKFLOW', 4));
        $privilege1->addPermission(new AclPermission('PERFORM_TRANSITIONS', 3));

        $privilege1Transition1 = new AclPrivilege();
        $privilege1Transition1->setIdentity(new AclPrivilegeIdentity('workflow:workflow1:transition1', 'transition11'));
        $privilege1Transition1->addPermission(new AclPermission('PERFORM_TRANSITION', 3));
        $privilege1Transition1->setDescription('Transition 11');

        $privilege1Transition2 = new AclPrivilege();
        $privilege1Transition2->setIdentity(new AclPrivilegeIdentity('workflow:workflow1:transition2', 'transition12'));
        $privilege1Transition2->addPermission(new AclPermission('PERFORM_TRANSITION', 1));

        $privilege1->setFields(new ArrayCollection([$privilege1Transition1, $privilege1Transition2]));

        $privilege2 = new AclPrivilege();
        $privilege2->setIdentity(new AclPrivilegeIdentity('workflow:workflow2', 'workflow 2'));
        $privilege2->addPermission(new AclPermission('VIEW_WORKFLOW', 1));
        $privilege2->addPermission(new AclPermission('PERFORM_TRANSITIONS', 2));

        $privilege2Transition1 = new AclPrivilege();
        $privilege2Transition1->setIdentity(new AclPrivilegeIdentity('workflow:workflow2:transition1', 'transition21'));
        $privilege2Transition1->addPermission(new AclPermission('PERFORM_TRANSITION', 4));

        $privilege2Transition2 = new AclPrivilege();
        $privilege2Transition2->setIdentity(new AclPrivilegeIdentity('workflow:workflow2:transition2', 'transition22'));
        $privilege2Transition2->addPermission(new AclPermission('PERFORM_TRANSITION', 3));

        $privilege2->setFields(new ArrayCollection([$privilege2Transition1, $privilege2Transition2]));

        $privileges = new ArrayCollection(['workflow' => new ArrayCollection([$privilege1, $privilege2])]);
        $this->aclRoleHandler->expects($this->any())->method('getAllPrivileges')->willReturn($privileges);

        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($value) {
                return 'translated: ' . $value;
            });

        $this->permissionManager->expects($this->any())->method('getPermissionByName')
            ->willReturnCallback(
                function ($permissionName) {
                    $permission = new Permission();
                    $permission->setName($permissionName);
                    $permission->setLabel($permissionName);
                    return $permission;
                }
            );

        $result = $this->datasource->getResults();

        $this->validateResult($result);
    }

    protected function validateResult($result)
    {
        $this->assertCount(2, $result);

        /** @var ResultRecord $item1 */
        $item1 = $result[0];
        $this->assertEquals('workflow:workflow1', $item1->getValue('identity'));
        $this->assertEquals('workflow 1', $item1->getValue('label'));
        $permissions = $item1->getValue('permissions');
        $this->assertCount(2, $permissions);
        $permission1 = $permissions[0];
        $this->assertEquals('VIEW_WORKFLOW', $permission1['name']);
        $this->assertEquals('translated: oro.workflow.permission.VIEW_WORKFLOW', $permission1['label']);
        $this->assertEquals('workflow:workflow1', $permission1['identity']);
        $this->assertEquals(4, $permission1['access_level']);
        $this->assertEquals('translated: commerce_role_prefix.GLOBAL', $permission1['access_level_label']);
        $permission2 = $permissions[1];
        $this->assertEquals('PERFORM_TRANSITIONS', $permission2['name']);
        $this->assertEquals('translated: oro.workflow.permission.PERFORM_TRANSITIONS', $permission2['label']);
        $this->assertEquals('workflow:workflow1', $permission2['identity']);
        $this->assertEquals(3, $permission2['access_level']);
        $this->assertEquals('translated: commerce_role_prefix.DEEP', $permission2['access_level_label']);
        $transitions1 = $item1->getValue('fields');
        $this->assertCount(2, $transitions1);
        $transition1 = $transitions1[0];
        $this->assertEquals('workflow:workflow1:transition1', $transition1['identity']);
        $this->assertEquals('transition11', $transition1['label']);
        $this->assertEquals('Transition 11', $transition1['description']);
        $permissions = $transition1['permissions'];
        $this->assertCount(1, $permissions);
        $permission1 = $permissions[0];
        $this->assertEquals('PERFORM_TRANSITION', $permission1['name']);
        $this->assertEquals('translated: oro.workflow.permission.PERFORM_TRANSITION', $permission1['label']);
        $this->assertEquals('workflow:workflow1:transition1', $permission1['identity']);
        $this->assertEquals(3, $permission1['access_level']);
        $this->assertEquals('translated: commerce_role_prefix.DEEP', $permission1['access_level_label']);
        $transition2 = $transitions1[1];
        $this->assertEquals('workflow:workflow1:transition2', $transition2['identity']);
        $this->assertEquals('transition12', $transition2['label']);
        $this->assertNull($transition2['description']);
        $permissions = $transition2['permissions'];
        $this->assertCount(1, $permissions);
        $permission1 = $permissions[0];
        $this->assertEquals('PERFORM_TRANSITION', $permission1['name']);
        $this->assertEquals('translated: oro.workflow.permission.PERFORM_TRANSITION', $permission1['label']);
        $this->assertEquals('workflow:workflow1:transition2', $permission1['identity']);
        $this->assertEquals(1, $permission1['access_level']);
        $this->assertEquals('translated: commerce_role_prefix.BASIC', $permission1['access_level_label']);
    }
}
