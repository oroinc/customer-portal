<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Form\Extension\AclAccessLevelSelectorExtension;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleType;
use Oro\Bundle\SecurityBundle\Form\Type\AclAccessLevelSelectorType;
use Oro\Component\Testing\Unit\Form\Type\Stub\FormStub;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class AclAccessLevelSelectorExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|RoleTranslationPrefixResolver */
    private $roleTranslationPrefixResolver;

    /** @var AclAccessLevelSelectorExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->roleTranslationPrefixResolver = $this->createMock(RoleTranslationPrefixResolver::class);

        $this->extension = new AclAccessLevelSelectorExtension($this->roleTranslationPrefixResolver);
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([AclAccessLevelSelectorType::class], AclAccessLevelSelectorExtension::getExtendedTypes());
    }

    /**
     * @dataProvider finishViewDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testFinishView(
        bool $hasPermissionForm = false,
        bool $hasPermissionsForm = false,
        bool $hasPrivilegeForm = false,
        bool $hasPrivilegesForm = false,
        bool $hasRoleForm = false,
        AbstractType $roleFormType = null,
        string $expectedPrefix = null
    ) {
        $this->roleTranslationPrefixResolver->expects($expectedPrefix ? $this->once() : $this->never())
            ->method('getPrefix')
            ->willReturn($expectedPrefix);

        $roleForm = null;
        if ($hasRoleForm) {
            $type = $this->createMock(ResolvedFormTypeInterface::class);
            $type->expects($this->any())
                ->method('getInnerType')
                ->willReturn($roleFormType);

            $formConfig = $this->createMock(FormConfigInterface::class);
            $formConfig->expects($this->once())
                ->method('getType')
                ->willReturn($type);

            $roleForm = $this->createMock(FormInterface::class);
            $roleForm->expects($this->once())
                ->method('getConfig')
                ->willReturn($formConfig);
        }

        $privilegesForm = null;
        if ($hasPrivilegesForm) {
            $privilegesForm = $this->createMock(FormInterface::class);
            $privilegesForm->expects($this->once())
                ->method('getParent')
                ->willReturn($roleForm);
        }

        $privilegeForm = null;
        if ($hasPrivilegeForm) {
            $privilegeForm = $this->createMock(FormInterface::class);
            $privilegeForm->expects($this->once())
                ->method('getParent')
                ->willReturn($privilegesForm);
        }

        $permissionsForm = null;
        if ($hasPermissionsForm) {
            $permissionsForm = $this->createMock(FormInterface::class);
            $permissionsForm->expects($this->once())
                ->method('getParent')
                ->willReturn($privilegeForm);
        }

        $permissionForm = null;
        if ($hasPermissionForm) {
            $permissionForm = $this->createMock(FormInterface::class);
            $permissionForm->expects($this->once())
                ->method('getParent')
                ->willReturn($permissionsForm);
        }

        $form = $this->createMock(FormInterface::class);
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

    public function finishViewDataProvider(): array
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
