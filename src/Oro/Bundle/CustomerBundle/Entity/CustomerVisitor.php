<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerVisitor
 *
 * @ORM\Table(
 *     name="oro_customer_visitor",
 *     uniqueConstraints = {
 *         @ORM\UniqueConstraint(
 *             name="oro_unq_cust_vis_session",
 *             columns = {"session_id"}
 *         )
 *     }
 * )
 * @ORM\Entity()
 */
class CustomerVisitor
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_visit", type="datetime")
     */
    private $lastVisit;

    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=255, nullable=false)
     */
    protected $sessionId;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lastVisit
     *
     * @param \DateTime $lastVisit
     *
     * @return CustomerVisitor
     */
    public function setLastVisit($lastVisit)
    {
        $this->lastVisit = $lastVisit;

        return $this;
    }

    /**
     * Get lastVisit
     *
     * @return \DateTime
     */
    public function getLastVisit()
    {
        return $this->lastVisit;
    }
}

