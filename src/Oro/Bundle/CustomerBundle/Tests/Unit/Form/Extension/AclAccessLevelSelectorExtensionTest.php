<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Form\Extension\AclAccessLevelSelectorExtension;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleType;
use Oro\Bundle\SecurityBundle\Form\Type\AclAccessLevelSelectorType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AclAccessLevelSelectorExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RoleTranslationPrefixResolver
     */
    protected $roleTranslationPrefixResolver;

    /**
     * @var AclAccessLevelSelectorExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->roleTranslationPrefixResolver = $this
            ->getMockBuilder('Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new AclAccessLevelSelectorExtension($this->roleTranslationPrefixResolver);
    }

    protected function tearDown()
    {
        unset($this->roleTranslationPrefixResolver, $this->extension);
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(AclAccessLevelSelectorType::NAME, $this->extension->getExtendedType());
    }

    /**
     * @param bool $hasPermissionForm
     * @param bool $hasPermissionsForm
     * @param bool $hasPrivilegeForm
     * @param bool $hasPrivilegesForm
     * @param bool $hasRoleForm
     * @param string|null $roleFormName
     * @param string|null $expectedPrefix
     * @dataProvider finishViewDataProvider
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testFinishView(
        $hasPermissionForm = false,
        $hasPermissionsForm = false,
        $hasPrivilegeForm = false,
        $hasPrivilegesForm = false,
        $hasRoleForm = false,
        $roleFormName = null,
        $expectedPrefix = null
    ) {
        $this->roleTranslationPrefixResolver->expects($expectedPrefix ? $this->once() : $this->never())
            ->method('getPrefix')
            ->willReturn($expectedPrefix);

        $roleForm = null;
        if ($hasRoleForm) {
            $type = $this->createMock('Symfony\Component\Form\ResolvedFormTypeInterface');
            $type->expects($this->once())
                ->method('getName')
                ->willReturn($roleFormName);

            $formConfig = $this->createMock('Symfony\Component\Form\FormConfigInterface');
            $formConfig->expects($this->once())
                ->method('getType')
                ->willReturn($type);

            $roleForm = $this->createMock('Symfony\Component\Form\FormInterface');
            $roleForm->expects($this->once())
                ->method('getConfig')
                ->willReturn($formConfig);
        }

        $privilegesForm = null;
        if ($hasPrivilegesForm) {
            $privilegesForm = $this->createMock('Symfony\Component\Form\FormInterface');
            $privilegesForm->expects($this->once())
                ->method('getParent')
                ->willReturn($roleForm);
        }

        $privilegeForm = null;
        if ($hasPrivilegeForm) {
            $privilegeForm = $this->createMock('Symfony\Component\Form\FormInterface');
            $privilegeForm->expects($this->once())
                ->method('getParent')
                ->willReturn($privilegesForm);
        }

        $permissionsForm = null;
        if ($hasPermissionsForm) {
            $permissionsForm = $this->createMock('Symfony\Component\Form\FormInterface');
            $permissionsForm->expects($this->once())
                ->method('getParent')
                ->willReturn($privilegeForm);
        }

        $permissionForm = null;
        if ($hasPermissionForm) {
            $permissionForm = $this->createMock('Symfony\Component\Form\FormInterface');
            $permissionForm->expects($this->once())
                ->method('getParent')
                ->willReturn($permissionsForm);
        }

        /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form */
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())
            ->method('getParent')
            ->willReturn($permissionForm);

        $formView = new FormView();

        $this->extension->finishView($formView, $form, []);

        if ($expectedPrefix) {
            $this->assertArrayHasKey('translation_prefix', $formView->vars);
            $this->assertEquals($expectedPrefix, $formView->vars['translation_prefix']);
        } else {
            $this->assertArrayNotHasKey('translation_prefix', $formView->vars);
        }
    }

    /**
     * @return array
     */
    public function finishViewDataProvider()
    {
        return [
            'no permission form' => [],
            'no permissions form' => [true],
            'no privilege form' => [true, true],
            'no privileges form' => [true, true, true],
            'no role form' => [true, true, true, true],
            'not supported form name' => [true, true, true, true, true, 'not_supported_form'],
            'supported form name (CustomerUserRoleType)' => [
                true,
                true,
                true,
                true,
                true,
                CustomerUserRoleType::NAME,
                'oro.customer.security.access-level.'
            ],
            'supported form name (FrontendCustomerUserRoleType)' => [
                true,
                true,
                true,
                true,
                true,
                FrontendCustomerUserRoleType::NAME,
                'oro.customer.security.frontend.access-level.'
            ],
        ];
    }
}
