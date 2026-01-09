<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Renders ACL access level as a text field with role permission translation prefix.
 *
 * This form type extends the standard TextType to display ACL access levels in a read-only
 * format with additional metadata about the privilege identity and translated access level name.
 * It is primarily used in role permission management interfaces to show the current access level
 * for a specific privilege.
 */
class CustomerAclAccessLevelTextType extends AbstractType
{
    public const NAME = 'oro_customer_acl_access_level_text';

    /**
     * @var RoleTranslationPrefixResolver
     */
    protected $roleTranslationPrefixResolver;

    public function __construct(RoleTranslationPrefixResolver $roleTranslationPrefixResolver)
    {
        $this->roleTranslationPrefixResolver = $roleTranslationPrefixResolver;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parent = $form->getParent()->getParent()->getParent();
        $parentData = $parent->getData();
        if ($parentData instanceof AclPrivilege) {
            $view->vars['identity'] = $parentData->getIdentity()->getId();
            $view->vars['level_label'] = AccessLevel::getAccessLevelName($form->getData());
        }

        //uses on view page for rendering preloaded string (role permission name)
        $view->vars['translation_prefix'] = $this->roleTranslationPrefixResolver->getPrefix();
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return static::NAME;
    }
}
