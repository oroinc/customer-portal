<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleType;
use Oro\Bundle\SecurityBundle\Form\Type\AclAccessLevelSelectorType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Extends the ACL access level selector form type to add role translation prefixes.
 */
class AclAccessLevelSelectorExtension extends AbstractTypeExtension
{
    /**
     * @var RoleTranslationPrefixResolver
     */
    protected $roleTranslationPrefixResolver;

    public function __construct(RoleTranslationPrefixResolver $roleTranslationPrefixResolver)
    {
        $this->roleTranslationPrefixResolver = $roleTranslationPrefixResolver;
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [AclAccessLevelSelectorType::class];
    }

    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $permissionForm = $form->getParent();
        if (!$permissionForm) {
            return;
        }

        $permissionsForm = $permissionForm->getParent();
        if (!$permissionsForm) {
            return;
        }

        $privilegeForm = $permissionsForm->getParent();
        if (!$privilegeForm) {
            return;
        }

        $privilegesForm = $privilegeForm->getParent();
        if (!$privilegesForm) {
            return;
        }

        $roleForm = $privilegesForm->getParent();
        if (!$roleForm) {
            return;
        }

        $formType = $roleForm->getConfig()->getType()->getInnerType();
        if (is_object($formType) && in_array(
            get_class($formType),
            [CustomerUserRoleType::class, FrontendCustomerUserRoleType::class]
        )) {
            //uses on edit page for rendering preloaded string (role permission name)
            $view->vars['translation_prefix'] = $this->roleTranslationPrefixResolver->getPrefix();
        }
    }
}
