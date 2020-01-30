<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * Delegates the handling of customer user reassign to child updaters.
 */
class CustomerUserReassignUpdater implements CustomerUserReassignUpdaterInterface
{
    /** @var iterable|CustomerUserReassignEntityUpdater[] */
    private $updaters;

    /**
     * @param iterable|CustomerUserReassignEntityUpdater[] $updaters
     */
    public function __construct(iterable $updaters)
    {
        $this->updaters = $updaters;
    }

    /**
     * {@inheritdoc}
     */
    public function update(CustomerUser $customerUser)
    {
        foreach ($this->updaters as $updater) {
            $updater->update($customerUser);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClassNamesToUpdate(CustomerUser $customerUser): array
    {
        $classNames = [];
        foreach ($this->updaters as $updater) {
            if ($updater->hasEntitiesToUpdate($customerUser)) {
                $classNames[] = $updater->getEntityClass();
            }
        }

        return array_unique($classNames);
    }
}
