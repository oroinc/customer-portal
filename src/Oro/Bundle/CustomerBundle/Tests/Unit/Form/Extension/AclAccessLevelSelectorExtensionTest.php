<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Form\Extension\AclAccessLevelSelectorExtension;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleType;
use Oro\Bundle\SecurityBundle\Form\Type\AclAccessLevelSelectorType;
use Oro\Component\Testing\Unit\Form\Type\Stub\FormStub;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AclAccessLevelSelectorExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RoleTranslationPrefixResolver
     */
    protected $roleTranslationPrefixResolver;

    /**
     * @var AclAccessLevelSelectorExtension
     */
    protected $extension;

    protected function setUp(): void
    {
        $this->roleTranslationPrefixResolver = $this
            ->getMockBuilder('Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new AclAccessLevelSelectorExtension($this->roleTranslationPrefixResolver);
    }

    protected function tearDown(): void
    {
        unset($this->roleTranslationPrefixResolver, $this->extension);
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([AclAccessLevelSelectorType::class], AclAccessLevelSelectorExtension::getExtendedTypes());
    }

    /**
     * @param bool $hasPermissionForm
     * @param bool $hasPermissionsForm
     * @param bool $hasPrivilegeForm
     * @param bool $hasPrivilegesForm
     * @param bool $hasRoleForm
     * @param string|null $roleFormType
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
        $roleFormType = null,
        $expectedPrefix = null
    ) {
        $this->roleTranslationPrefixResolver->expects($expectedPrefix ? $this->once() : $this->never())
            ->method('getPrefix')
            ->willReturn($expectedPrefix);

        $roleForm = null;
        if ($hasRoleForm) {
            $type = $this->createMock('Symfony\Component\Form\ResolvedFormTypeInterface');
            $type->expects($this->any())
                ->method('getInnerType')
                ->willReturn($roleFormType);

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

        /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject $form */
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
            'not supported form name' => [true, true, true, true, true, new FormStub('not_supported_form')],
            'supported form name (CustomerUserRoleType)' => [
                true,
                true,
                true,
                true,
                true,
                new CustomerUserRoleType(),
                'oro.customer.security.access-level.'
            ],
            'supported form name (FrontendCustomerUserRoleType)' => [
                true,
                true,
                true,
                true,
                true,
                new FrontendCustomerUserRoleType(),
                'oro.customer.security.frontend.access-level.'
            ],
        ];
    }
}
