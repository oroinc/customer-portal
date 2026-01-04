<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\EmailBundle\Form\Type\EmailAddressType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Email form for visitor
 */
class CustomerVisitorEmailAddressType extends EmailAddressType
{
    public const NAME = 'oro_customer_visitor_email_address';

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return EmailAddressType::class;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $emailConstaraint = new Email(['mode' => Email::VALIDATION_MODE_STRICT ]);

        $resolver->setDefault('multiple', false);

        if ($this->isGuest()) {
            $resolver->setDefaults([
                'required' => true,
                'constraints' => [new NotBlank(), $emailConstaraint]
            ]);
        } else {
            $resolver->setDefault('constraints', [$emailConstaraint]);
        }
    }

    /**
     * Forces setting required (asterisk) for Guest
     */
    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($this->isGuest()) {
            $view->vars['required'] = true;
        }
    }

    private function isGuest(): bool
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof AnonymousCustomerUserToken;
    }

    #[\Override]
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
