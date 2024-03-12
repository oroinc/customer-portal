<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tbl_customer')]
class TestCustomer
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TestCustomer::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    protected ?TestCustomer $parent = null;

    /**
     * @var Collection<int, TestCustomer>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: TestCustomer::class)]
    protected ?Collection $children = null;

    /**
     * @var Collection<int, TestCustomerUser>
     */
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: TestCustomerUser::class)]
    protected ?Collection $users = null;

    #[ORM\ManyToOne(targetEntity: TestOrganization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id')]
    protected ?TestOrganization $organization = null;
}
