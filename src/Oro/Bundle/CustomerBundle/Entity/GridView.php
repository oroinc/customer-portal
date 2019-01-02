<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataGridBundle\Entity\AbstractGridView;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Grid view entity for customer users.
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\CustomerBundle\Entity\Repository\GridViewRepository")
 * @Config(
 *      defaultValues={
 *          "ownership"={
 *              "frontend_owner_type"="FRONTEND_USER",
 *              "frontend_owner_field_name"="customerUserOwner",
 *              "frontend_owner_column_name"="customer_user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="commerce",
 *              "category"="account_management"
 *          }
 *      }
 * )
 * @UniqueEntity(
 *      fields={"name", "customerUserOwner", "gridName", "organization"},
 *      message="oro.datagrid.gridview.unique"
 * )
 */
class GridView extends AbstractGridView
{
    /**
     * {@inheritdoc}
     *
     * @ORM\OneToMany(
     *      targetEntity="Oro\Bundle\CustomerBundle\Entity\GridViewUser",
     *      mappedBy="gridView",
     *      cascade={"ALL"},
     *      fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinTable(name="oro_grid_view_user_rel",
     *     joinColumns={@ORM\JoinColumn(name="id", referencedColumnName="grid_view_id", onDelete="CASCADE")}
     * )
     */
    protected $users;

    /**
     * @var CustomerUser
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CustomerBundle\Entity\CustomerUser")
     * @ORM\JoinColumn(name="customer_user_owner_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank
     */
    protected $customerUserOwner;

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->customerUserOwner;
    }

    /**
     * {@inheritdoc}
     */
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
