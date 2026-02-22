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
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_granted_view_customer_user', [$this, 'isGrantedViewCustomerUser']),
            new TwigFunction('oro_customer_parent_parts', [$this, 'getCustomerParentParts']),
        ];
    }

    public function isGrantedViewCustomerUser(mixed $object): bool
    {
        return $this->getCustomerUserProvider()->isGrantedViewCustomerUser($object);
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

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            CustomerUserProvider::class,
            LoggerInterface::class
        ];
    }

    private function getCustomerUserProvider(): CustomerUserProvider
    {
        return $this->container->get(CustomerUserProvider::class);
    }

    private function getLogger(): LoggerInterface
    {
        return $this->container->get(LoggerInterface::class);
    }
}
