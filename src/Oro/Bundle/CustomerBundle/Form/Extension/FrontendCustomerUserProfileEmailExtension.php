<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserProfileEmailType;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Changes the email field to newEmail if the customer_user_email_change_verification_enabled is enabled.
 */
class FrontendCustomerUserProfileEmailExtension extends AbstractTypeExtension
{
    public function __construct(
        private FeatureChecker $featureChecker,
        private CustomerUserManager $customerUserManager,
        private TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->featureChecker->isFeatureEnabled('customer_user_email_change_verification_enabled')) {
            $builder->remove('email');
            $builder->add(
                'newEmailAddress',
                EmailType::class,
                [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'oro.customer.customeruser.email.label_short',

                    'constraints' => [
                        new Email()
                    ]
                ]
            );
            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        }
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [FrontendCustomerUserProfileEmailType::class];
    }

    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm()->get('newEmailAddress');
        $data = $form->getData();
        $existingCustomerUser = $this->customerUserManager->findUserByEmail($data);
        if (null !== $existingCustomerUser) {
            $form->addError(new FormError($this->translator->trans('oro.customer.message.user_customer_exists')));
        } else {
            $event->getData()->setNewEmail($data);
        }
    }
}
