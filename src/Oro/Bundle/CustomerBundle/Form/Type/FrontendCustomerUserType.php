<?php

namespace Oro\Bundle\CustomerBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Storefront Form Type for Customer User
 */
class FrontendCustomerUserType extends AbstractType
{
    const NAME = 'oro_customer_frontend_customer_user';

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var string */
    protected $customerUserClass;

    /** @var WebsiteManager */
    protected $websiteManager;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenAccessorInterface $tokenAccessor,
        WebsiteManager $websiteManager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenAccessor = $tokenAccessor;
        $this->websiteManager = $websiteManager;
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
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        $builder->remove('salesRepresentatives');
        $builder->remove('addresses');
        if ($this->authorizationChecker->isGranted('oro_customer_frontend_customer_user_role_view')) {
            $builder->add(
                'userRoles',
                FrontendCustomerUserRoleSelectType::class,
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

        $event->getForm()->add('customer', FrontendOwnerSelectType::class, [
            'label' => 'oro.customer.customer.entity_label',
            'targetObject' => $data
        ]);

        $data->setOrganization($user->getOrganization());
    }

    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof CustomerUser && !$data->getId() && null === $data->getWebsite()) {
            $currentWebsite = $this->websiteManager->getCurrentWebsite();
            if ($currentWebsite) {
                $data->setWebsite($currentWebsite);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CustomerUserType::class;
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
