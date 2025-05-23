<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerUserRoleSelectType extends AbstractType
{
    const NAME = 'oro_customer_customer_user_role_select';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @var string
     */
    protected $roleClass;

    /**
     * @param string $roleClass
     */
    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => $this->roleClass,
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'choice_label' => function ($role) {
                if (!($role instanceof CustomerUserRole)) {
                    return (string)$role;
                }

                $roleType = 'oro.customer.customeruserrole.type.';
                $roleType .= $role->isPredefined() ? 'predefined.label' : 'customizable.label';
                return sprintf('%s (%s)', $role->getLabel(), $this->translator->trans($roleType));
            }
        ]);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
