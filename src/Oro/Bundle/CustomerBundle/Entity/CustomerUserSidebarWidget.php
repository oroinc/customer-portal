<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\SidebarBundle\Entity\AbstractWidget;
use Oro\Bundle\SidebarBundle\Entity\Repository\WidgetRepository;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Represents a sidebar widget.
 */
#[ORM\Entity(repositoryClass: WidgetRepository::class)]
#[ORM\Table(name: 'oro_customer_user_sdbar_wdg')]
#[ORM\Index(columns: ['customer_user_id', 'placement'], name: 'oro_cus_sdbr_wdgs_usr_place_idx')]
#[ORM\Index(columns: ['position'], name: 'oro_cus_sdar_wdgs_pos_idx')]
class CustomerUserSidebarWidget extends AbstractWidget
{
    /**
     * @var CustomerUser|null
     */
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?AbstractUser $user = null;
}
