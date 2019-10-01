<?php

namespace Oro\Bundle\CustomerBundle\Twig;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Twig function to check if custom user has access to view an entity:
 *   - is_granted_view_customer_user
 * Provides a Twig function to build customer parent chain:
 *   - oro_customer_parent_parts
 */
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
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->container->get('logger');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_granted_view_customer_user', [$this, 'isGrantedViewCustomerUser']),
            new \Twig_SimpleFunction('oro_customer_parent_parts', [$this, 'getCustomerParentParts']),
        ];
    }
    /**
     * @param Customer $customer
     * @return array
     */
    public function getCustomerParentParts(Customer $customer): array
    {
        $parts = [];
        $i = 0;
        while ($customer->getParent() && $customer->getParent()->getName()) {
            if ($i++ > 50) {
                $this->getLogger()->warning(
                    sprintf('Customer parent loop limit was reached for customer #%s.', $customer->getId())
                );
                break;
            }
            $customer = $customer->getParent();
            $parts[] = [
                'name' => $customer->getName(),
                'id' => $customer->getId(),
            ];
        }

        return array_reverse($parts);
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
