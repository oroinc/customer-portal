<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\NavigationBundle\Entity\AbstractNavigationItem;
use Oro\Bundle\NavigationBundle\Entity\Repository\NavigationItemRepository;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Navigation Entity
 */
#[ORM\Entity(repositoryClass: NavigationItemRepository::class)]
#[ORM\Table(name: 'oro_cus_navigation_item')]
#[ORM\Index(columns: ['customer_user_id', 'position'], name: 'oro_sorted_items_idx')]
#[ORM\HasLifecycleCallbacks]
class NavigationItem extends AbstractNavigationItem
{
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?AbstractUser $user = null;

    #[ORM\Column(name: 'type', type: Types::STRING, length: 20, nullable: false)]
    protected ?string $type = null;
}
