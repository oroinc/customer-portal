<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\UserBundle\Form\EventListener\ChangeRoleSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Base class for the CustomerUserRole form type.
 */
abstract class AbstractCustomerUserRoleType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param string $dataClass
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'label',
                TextType::class,
                [
                    'label' => 'oro.customer.customeruserrole.role.label',
                    'required' => true,
                    'constraints' => [new Length(['min' => 3, 'max' => 32, 'allowEmptyString' => false])]
                ]
            )
            ->add(
                'appendUsers',
                EntityIdentifierType::class,
                [
                    'class'    => 'Oro\Bundle\CustomerBundle\Entity\CustomerUser',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true
                ]
            )
            ->add(
                'removeUsers',
                EntityIdentifierType::class,
                [
                    'class'    => 'Oro\Bundle\CustomerBundle\Entity\CustomerUser',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true
                ]
            );
        if (!$options['hide_self_managed']) {
            $builder->add(
                'selfManaged',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'oro.customer.customeruserrole.self_managed.label'
                ]
            );
        }
        $builder->add(
            'privileges',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );

        $builder->addEventSubscriber(new ChangeRoleSubscriber());
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var CustomerUserRole|null $role */
            $role = $event->getData();

            // set role if it's not defined yet
            if ($role && !$role->getRole()) {
                $label = $role->getLabel();
                if ($label) {
                    $role->setRole($label);
                }
            }
        }, 10);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['privilege_config']);
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'access_level_route' => 'oro_customer_acl_access_levels',
            'hide_self_managed' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['privilegeConfig'] = $options['privilege_config'];
    }
}
