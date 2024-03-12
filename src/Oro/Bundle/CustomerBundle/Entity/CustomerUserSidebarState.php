<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\SidebarBundle\Entity\AbstractSidebarState;
use Oro\Bundle\SidebarBundle\Entity\Repository\SidebarStateRepository;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Sidebar state storage
 */
#[ORM\Entity(repositoryClass: SidebarStateRepository::class)]
#[ORM\Table(name: 'oro_customer_user_sdbar_st')]
#[ORM\UniqueConstraint(name: 'oro_cus_sdbar_st_unq_idx', columns: ['customer_user_id', 'position'])]
class CustomerUserSidebarState extends AbstractSidebarState
{
    /**
     * @var CustomerUser|null
     */
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?AbstractUser $user = null;
}
