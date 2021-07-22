<?php

namespace Oro\Bundle\CustomerBundle\Twig;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function to check if custom user has access to view an entity:
 *   - is_granted_view_customer_user
 * Provides a Twig function to build customer parent chain:
 *   - oro_customer_parent_parts
 */
class CustomerExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    protected $container;

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
        return $this->container->get(LoggerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('is_granted_view_customer_user', [$this, 'isGrantedViewCustomerUser']),
            new TwigFunction('oro_customer_parent_parts', [$this, 'getCustomerParentParts']),
        ];
    }

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
    public static function getSubscribedServices()
    {
        return [
            'oro_customer.security.customer_user_provider' => CustomerUserProvider::class,
            LoggerInterface::class,
        ];
    }
}
