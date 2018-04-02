<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FrontendCustomerUserType extends AbstractType
{
    const NAME = 'oro_customer_frontend_customer_user';

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var string */
    protected $customerUserClass;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenAccessorInterface        $tokenAccessor
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param string $class
     */
    public function setCustomerUserClass($class)
    {
        $this->customerUserClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->remove('salesRepresentatives');
        $builder->remove('addresses');
        if ($this->authorizationChecker->isGranted('oro_customer_frontend_customer_user_role_view')) {
            $builder->add(
                'roles',
                FrontendCustomerUserRoleSelectType::NAME,
                [
                    'label' => 'oro.customer.customeruser.roles.label',
                ]
            );
        }
    }

    /**
     * @param FormEvent $event
     * @return bool
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var $user CustomerUser */
        $user = $this->tokenAccessor->getUser();
        if (!$user instanceof CustomerUser) {
            return;
        }
        /** @var CustomerUser $data */
        $data = $event->getData();

        $event->getForm()->add('customer', FrontendOwnerSelectType::NAME, [
            'label' => 'oro.customer.customer.entity_label',
            'targetObject' => $data
        ]);

        $data->setOrganization($user->getOrganization());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CustomerUserType::NAME;
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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->customerUserClass,
            'ownership_disabled' => true,
        ]);
    }
}
