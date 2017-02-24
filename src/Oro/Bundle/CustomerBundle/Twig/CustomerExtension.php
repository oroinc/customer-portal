<?php

namespace Oro\Bundle\CustomerBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;

class CustomerExtension extends \Twig_Extension
{
    const NAME = 'customer_extension';

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return CustomerUserProvider
     */
    protected function getCustomerUserProvider()
    {
        return $this->container->get('oro_customer.security.customer_user_provider');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_granted_view_customer_user', [$this, 'isGrantedViewCustomerUser']),
        ];
    }

    /**
     * @param string $object
     *
     * @return bool
     */
    public function isGrantedViewCustomerUser($object)
    {
        return $this->getCustomerUserProvider()->isGrantedViewCustomerUser($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
