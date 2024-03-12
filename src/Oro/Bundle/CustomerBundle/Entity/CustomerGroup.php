<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCustomerBundle_Entity_CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerGroupRepository;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerGroupSelectType;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\UserBundle\Entity\Ownership\UserAwareTrait;

/**
 * CustomerGroup entity
 *
 * @mixin OroCustomerBundle_Entity_CustomerGroup
 */
#[ORM\Entity(repositoryClass: CustomerGroupRepository::class)]
#[ORM\Table(name: 'oro_customer_group')]
#[ORM\Index(columns: ['name'], name: 'oro_customer_group_name_idx')]
#[Config(
    routeName: 'oro_customer_customer_group_index',
    routeView: 'oro_customer_customer_group_view',
    routeUpdate: 'oro_customer_customer_group_update',
    defaultValues: [
        'entity' => ['icon' => 'fa-users'],
        'form' => ['form_type' => CustomerGroupSelectType::class, 'grid_name' => 'customer-groups-select-grid'],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'user_owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => 'commerce'],
        'dataaudit' => ['auditable' => true]
    ]
)]
class CustomerGroup implements OrganizationAwareInterface, ExtendEntityInterface
{
    use UserAwareTrait;
    use ExtendEntityTrait;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['identity' => true, 'order' => 10]]
    )]
    protected ?string $name = null;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return CustomerGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }
}
