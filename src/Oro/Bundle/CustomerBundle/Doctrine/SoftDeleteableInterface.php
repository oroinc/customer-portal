<?php

namespace Oro\Bundle\CustomerBundle\Doctrine;

/**
 * Defines the contract for entities that support soft deletion.
 *
 * Soft-deletable entities maintain a deletion timestamp instead of being permanently removed from the database.
 * This allows for data recovery and maintains referential integrity while logically marking records as deleted.
 */
interface SoftDeleteableInterface
{
    const FIELD_NAME = 'deletedAt';
    const NAME = 'Oro\Bundle\CustomerBundle\Doctrine\SoftDeleteableInterface';

    /**
     * @return \DateTime
     */
    public function getDeletedAt();

    /**
     * @param \DateTime|null $date
     * @return $this
     */
    public function setDeletedAt(?\DateTime $date = null);
}
