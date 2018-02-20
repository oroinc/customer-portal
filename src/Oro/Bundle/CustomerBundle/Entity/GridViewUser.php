<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Entity\AbstractGridViewUser;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\CustomerBundle\Entity\Repository\GridViewUserRepository")
 */
class GridViewUser extends AbstractGridViewUser
{
    /**
     * @var AbstractGridView
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\GridView", inversedBy="users")
     * @ORM\JoinColumn(name="grid_view_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $gridView;

    /**
     * @var CustomerUser
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser", cascade={"persist"})
     * @ORM\JoinColumn(name="customer_user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $customerUser;

    /**
     * {@inheritdoc}
     */
    public function setUser(AbstractUser $user = null)
    {
        $this->customerUser = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->customerUser;
    }
}
