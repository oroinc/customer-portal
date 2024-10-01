<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\Repository\WindowsStateRepository;
use Oro\Bundle\WindowsBundle\Entity\AbstractWindowsState;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Window state entity
 */
#[ORM\Entity(repositoryClass: WindowsStateRepository::class)]
#[ORM\Table(name: 'oro_cus_windows_state')]
#[ORM\Index(columns: ['customer_user_id'], name: 'oro_cus_windows_state_acu_idx')]
class WindowsState extends AbstractWindowsState
{
    #[ORM\ManyToOne(targetEntity: CustomerUserIdentity::class)]
    #[ORM\JoinColumn(name: 'customer_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?CustomerUserIdentity $user = null;

    #[\Override]
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    #[\Override]
    public function getUser()
    {
        return $this->user;
    }
}
