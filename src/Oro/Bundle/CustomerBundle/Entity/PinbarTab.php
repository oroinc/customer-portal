<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\Repository\PinbarTabRepository;
use Oro\Bundle\NavigationBundle\Entity\AbstractPinbarTab;
use Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface;

/**
 * Pinbar Tab Entity
 */
#[ORM\Entity(repositoryClass: PinbarTabRepository::class)]
#[ORM\Table(name: 'oro_cus_nav_item_pinbar')]
#[ORM\HasLifecycleCallbacks]
class PinbarTab extends AbstractPinbarTab
{
    #[ORM\OneToOne(targetEntity: NavigationItem::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?NavigationItemInterface $item = null;
}
