<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Updates the search result item URL to link directly to the parent Customer view instead of the address record.
 */
class RedirectCustomerAddressSearchToCustomerListener
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ManagerRegistry $doctrine
    ) {
    }

    public function process(PrepareResultItemEvent $event): void
    {
        $item = $event->getResultItem();
        if ($item->getEntityName() !== CustomerAddress::class) {
            return;
        }

        $address = $event->getEntity();
        if (null === $address) {
            $address = $this->doctrine
                ->getRepository(CustomerAddress::class)
                ->find($item->getRecordId());
        }

        $customer = $address?->getFrontendOwner();
        if (null === $customer) {
            return;
        }

        $item->setRecordUrl(
            $this->urlGenerator->generate(
                'oro_customer_customer_view',
                ['id' => $customer->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }
}
