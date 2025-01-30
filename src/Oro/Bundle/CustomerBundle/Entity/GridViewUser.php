<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\Repository\GridViewUserRepository;
use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\DataGridBundle\Entity\AbstractGridViewUser;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
* Entity that represents Grid View User
*
*/
#[ORM\Entity(repositoryClass: GridViewUserRepository::class)]
class GridViewUser extends AbstractGridViewUser
{
    #[ORM\ManyToOne(targetEntity: GridView::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'grid_view_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?AbstractGridView $gridView = null;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?CustomerUser $customerUser = null;

    #[\Override]
    public function setUser(?AbstractUser $user = null)
    {
        $this->customerUser = $user;

        return $this;
    }

    #[\Override]
    public function getUser()
    {
        return $this->customerUser;
    }
}
