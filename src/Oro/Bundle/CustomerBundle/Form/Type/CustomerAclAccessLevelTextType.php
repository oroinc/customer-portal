<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Acl\Resolver\RoleTranslationPrefixResolver;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class CustomerAclAccessLevelTextType extends AbstractType
{
    const NAME = 'oro_customer_acl_access_level_text';

    /**
     * @var RoleTranslationPrefixResolver
     */
    protected $roleTranslationPrefixResolver;

    public function __construct(RoleTranslationPrefixResolver $roleTranslationPrefixResolver)
    {
        $this->roleTranslationPrefixResolver = $roleTranslationPrefixResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return static::NAME;
    }
}
