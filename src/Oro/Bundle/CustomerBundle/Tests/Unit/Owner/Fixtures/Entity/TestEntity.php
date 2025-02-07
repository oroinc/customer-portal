<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

class TestEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $owner;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @var CustomerUser
     */
    private $customerUser;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @param int $id
     * @param User|null $owner
     * @param Organization|null $organization
     * @param CustomerUser|null $customerUser
     * @param Customer|null $customer
     */
    public function __construct(
        $id = 0,
        ?User $owner = null,
        ?Organization $organization = null,
        ?CustomerUser $customerUser = null,
        ?Customer $customer = null
    ) {
        $this->id = $id;
        $this->owner = $owner;
        $this->organization = $organization;
        $this->customerUser = $customerUser;
        $this->customer = $customer;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(?User $owner = null)
    {
        $this->owner = $owner;
    }

    public function setOrganization(?Organization $organization = null)
    {
        $this->organization = $organization;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    public function setCustomerUser(?CustomerUser $customerUser = null)
    {
        $this->customerUser = $customerUser;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer = null)
    {
        $this->customer = $customer;
    }
}
