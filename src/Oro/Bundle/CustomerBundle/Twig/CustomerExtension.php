<?php

namespace Oro\Bundle\CustomerBundle\Twig;

use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function to check if custom user has access to view an entity:
 *   - is_granted_view_customer_user
 */
class CustomerExtension extends AbstractExtension implements ServiceSubscriberInterface
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
            new TwigFunction('is_granted_view_customer_user', [$this, 'isGrantedViewCustomerUser']),
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_customer.security.customer_user_provider' => CustomerUserProvider::class,
        ];
    }
}
