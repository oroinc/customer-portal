<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'tbl_customer_user')]
class TestCustomerUser
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TestCustomer::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    protected ?TestCustomer $customer = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;
}
