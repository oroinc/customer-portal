<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\NavigationBundle\Entity\AbstractNavigationHistoryItem;
use Oro\Bundle\NavigationBundle\Entity\Repository\HistoryItemRepository;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Frontend Navigation History Entity
 */
#[ORM\Entity(repositoryClass: HistoryItemRepository::class)]
#[ORM\Table(name: 'oro_cus_navigation_history')]
#[ORM\Index(columns: ['route'], name: 'oro_acc_nav_history_route_idx')]
#[ORM\Index(columns: ['entity_id'], name: 'oro_acc_nav_history_entity_id_idx')]
#[ORM\HasLifecycleCallbacks]
class NavigationHistoryItem extends AbstractNavigationHistoryItem
{
    /**
     * @var CustomerUser|null $user
     */
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?AbstractUser $user = null;
}
