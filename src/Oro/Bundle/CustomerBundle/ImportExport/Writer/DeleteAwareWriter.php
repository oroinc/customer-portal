<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Writer;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\AbstractDefaultTypedAddressRepository;
use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;

/**
 * Entity writer with support of removal of entities marked for removal.
 */
class DeleteAwareWriter extends PersistentBatchWriter
{
    protected function saveItems(array $items, EntityManager $em)
    {
        $context = $this->contextRegistry->getByStepExecution($this->stepExecution);
        $itemsMarkedForRemoval = (array)$context->getValue('marked_for_removal');

        if ($itemsMarkedForRemoval) {
            foreach ($items as $item) {
                if ($item->getId() && in_array($item->getId(), $itemsMarkedForRemoval, true)) {
                    $em->remove($item);
                }
            }
        }
        $items = array_filter(
            $items,
            fn ($item) => !in_array($item->getId(), $itemsMarkedForRemoval, true)
        );

        parent::saveItems($items, $em);

        $this->updatePrimaryAndDefaultRecords($items, $em);

        $context->setValue('marked_for_removal', []);
    }

    private function updatePrimaryAndDefaultRecords(array $items, EntityManager $em): void
    {
        if (!$items) {
            return;
        }

        $primaryAddressesByOwner = [];
        $defaultAddressesByOwner = [];

        /** @var AbstractDefaultTypedAddress $item */
        foreach ($items as $item) {
            $ownerId = $item->getFrontendOwner()->getId();
            if ($item->isPrimary()) {
                $primaryAddressesByOwner[$ownerId] = $item->getId();
            }
            if ($item->hasDefault(AddressType::TYPE_SHIPPING)) {
                $defaultAddressesByOwner[AddressType::TYPE_SHIPPING][$ownerId] = $item->getId();
            }
            if ($item->hasDefault(AddressType::TYPE_BILLING)) {
                $defaultAddressesByOwner[AddressType::TYPE_BILLING][$ownerId] = $item->getId();
            }
        }

        $item = reset($items);
        $itemClass = ClassUtils::getClass($item);
        /** @var AbstractDefaultTypedAddressRepository $repo */
        $repo = $em->getRepository($itemClass);
        if ($primaryAddressesByOwner) {
            $repo->updateNotListedPrimaryAddresses($primaryAddressesByOwner);
        }
        if (!empty($defaultAddressesByOwner[AddressType::TYPE_SHIPPING])) {
            $repo->updateNotListedDefaultAddresses(
                AddressType::TYPE_SHIPPING,
                $defaultAddressesByOwner[AddressType::TYPE_SHIPPING]
            );
        }
        if (!empty($defaultAddressesByOwner[AddressType::TYPE_BILLING])) {
            $repo->updateNotListedDefaultAddresses(
                AddressType::TYPE_BILLING,
                $defaultAddressesByOwner[AddressType::TYPE_BILLING]
            );
        }
    }
}
