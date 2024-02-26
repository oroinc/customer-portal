<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\NavigationBundle\Entity\AbstractPageState;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
 * Page state entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'oro_cus_pagestate')]
#[ORM\HasLifecycleCallbacks]
class PageState extends AbstractPageState
{
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?AbstractUser $user = null;
}
