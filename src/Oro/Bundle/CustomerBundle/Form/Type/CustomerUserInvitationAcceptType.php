<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;

/**
 * Public form that lets the invited person complete their own profile.
 */
class CustomerUserInvitationAcceptType extends AbstractType
{
    public const NAME = 'oro_customer_frontend_customer_user_invitation_accept';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->has('companyName')) {
            $builder->remove('companyName');
        }

        $builder->add('email', EmailType::class, [
            'required' => true,
            'label' => 'oro.customer.customeruser.email.label_short',
            'disabled' => true,
            'attr' => ['placeholder' => 'oro.customer.customeruser.placeholder.email']
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerUser::class,
            'csrf_token_id' => 'customer_user_invitation_accept',
            'validation_groups' => static fn (FormInterface $form): array => $form->has('plainPassword')
                ? [Constraint::DEFAULT_GROUP, 'create']
                : [Constraint::DEFAULT_GROUP]
        ]);
    }

    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset($view->children['email'])) {
            $view->children = ['email' => $view->children['email']] + $view->children;
        }
    }

    #[\Override]
    public function getParent(): string
    {
        return FrontendCustomerUserRegistrationType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
