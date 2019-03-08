<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Combines CustomerUserReassignEntityUpdaters which it gets from CustomerUserReassignUpdaterPass compiler pass.
 * Used to call update methods from all CustomerUserReassignEntityUpdaters and to get entity class names which
 * need to be updated
 */
class CustomerUserReassignUpdater implements CustomerUserReassignUpdaterInterface
{
    /**
     * @var CustomerUserReassignEntityUpdater[]
     */
    private $customerUserReassignEntityUpdaters = [];

    /**
     * @param CustomerUserReassignEntityUpdater $customerUserReassignEntityUpdater
     */
    public function addCustomerUserReassignEntityUpdater(
        CustomerUserReassignEntityUpdater $customerUserReassignEntityUpdater
    ) {
        $this->customerUserReassignEntityUpdaters[
            $customerUserReassignEntityUpdater->getEntityClass()
        ] = $customerUserReassignEntityUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update(CustomerUser $customerUser)
    {
        foreach ($this->customerUserReassignEntityUpdaters as $customerUserReassignEntityUpdater) {
            $customerUserReassignEntityUpdater->update($customerUser);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClassNamesToUpdate(CustomerUser $customerUser): array
    {
        $classNames = [];

        foreach ($this->customerUserReassignEntityUpdaters as $customerUserReassignEntityUpdater) {
            if ($customerUserReassignEntityUpdater->hasEntitiesToUpdate($customerUser)) {
                $classNames[] = $customerUserReassignEntityUpdater->getEntityClass();
            }
        }

        return array_unique($classNames);
    }
}
