<?php

namespace Oro\Bundle\CustomerBundle\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait that implements {@see \Oro\Bundle\CustomerBundle\Doctrine\SoftDeleteableInterface}
 */
trait SoftDeleteableTrait
{
    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    protected $deletedAt;

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime|null $date
     * @return $this
     */
    public function setDeletedAt(?\DateTime $date = null)
    {
        $this->deletedAt = $date;

        return $this;
    }
}
