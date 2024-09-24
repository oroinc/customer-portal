<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\Repository\GridViewRepository;
use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Grid view entity for customer users.
 */
#[ORM\Entity(repositoryClass: GridViewRepository::class)]
#[UniqueEntity(
    fields: ['name', 'customerUserOwner', 'gridName', 'organization'],
    message: 'oro.datagrid.gridview.unique'
)]
#[Config(
    defaultValues: [
        'ownership' => [
            'frontend_owner_type' => 'FRONTEND_USER',
            'frontend_owner_field_name' => 'customerUserOwner',
            'frontend_owner_column_name' => 'customer_user_owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce', 'category' => 'account_management']
    ]
)]
class GridView extends AbstractGridView
{
    /**
     * @var Collection<int, GridViewUser>
     */
    #[ORM\JoinTable(name: 'oro_grid_view_user_rel')]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'grid_view_id', onDelete: 'CASCADE')]
    #[ORM\OneToMany(mappedBy: 'gridView', targetEntity: GridViewUser::class, cascade: ['ALL'], fetch: 'EXTRA_LAZY')]
    protected ?Collection $users = null;

    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    #[ORM\JoinColumn(name: 'customer_user_owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Assert\NotBlank]
    protected ?CustomerUser $customerUserOwner = null;

    #[\Override]
    public function getOwner()
    {
        return $this->customerUserOwner;
    }

    #[\Override]
    public function setOwner(AbstractUser $owner = null)
    {
        $this->customerUserOwner = $owner;

        return $this;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUserOwner()
    {
        return $this->getOwner();
    }

    /**
     * @param CustomerUser|null $owner
     *
     * @return AbstractGridView
     */
    public function setCustomerUserOwner(CustomerUser $owner = null)
    {
        return $this->setOwner($owner);
    }
}
