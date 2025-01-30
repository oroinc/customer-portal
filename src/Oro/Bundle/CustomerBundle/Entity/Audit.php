<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataAuditBundle\Entity\AbstractAudit;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\UserBundle\Entity\AbstractUser;

/**
* Entity that represents Audit
*
*/
#[ORM\Entity]
#[Config(defaultValues: ['security' => []])]
class Audit extends AbstractAudit
{
    #[ORM\ManyToOne(targetEntity: CustomerUser::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?CustomerUser $customerUser = null;

    #[\Override]
    public function setUser(?AbstractUser $user = null)
    {
        $this->customerUser = $user;
    }

    #[\Override]
    public function getUser()
    {
        return $this->customerUser;
    }
}
