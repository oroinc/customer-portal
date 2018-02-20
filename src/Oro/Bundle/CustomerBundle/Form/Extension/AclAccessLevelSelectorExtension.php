<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleType;
use Oro\Bundle\SecurityBundle\Form\Type\AclAccessLevelSelectorType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class AclAccessLevelSelectorExtension extends AbstractTypeExtension
{
    /**
     * @var RoleTranslationPrefixResolver
     */
    protected $roleTranslationPrefixResolver;

    /**
     * @param RoleTranslationPrefixResolver $roleTranslationPrefixResolver
     */
    public function __construct(RoleTranslationPrefixResolver $roleTranslationPrefixResolver)
    {
        $this->roleTranslationPrefixResolver = $roleTranslationPrefixResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return AclAccessLevelSelectorType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
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

        if (in_array(
            $roleForm->getConfig()->getType()->getName(),
            [CustomerUserRoleType::NAME, FrontendCustomerUserRoleType::NAME]
        )) {
            //uses on edit page for rendering preloaded string (role permission name)
            $view->vars['translation_prefix'] = $this->roleTranslationPrefixResolver->getPrefix();
        }
    }
}
