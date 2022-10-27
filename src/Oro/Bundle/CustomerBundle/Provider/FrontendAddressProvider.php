<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerAddressRepository;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserAddressRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Frontend provider for address entities.
 */
class FrontendAddressProvider
{
    /** @var ManagerRegistry */
    private $registry;

    /** @var AclHelper */
    private $aclHelper;

    /** @var string */
    private $customerAddressClass;

    /** @var string */
    private $customerUserAddressClass;

    /** @var array */
    private $cache = [];

    public function __construct(
        ManagerRegistry $registry,
        AclHelper $aclHelper,
        string $customerAddressClass,
        string $customerUserAddressClass
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;

        $this->customerAddressClass = $customerAddressClass;
        $this->customerUserAddressClass = $customerUserAddressClass;
    }

    /**
     * @return CustomerAddress[]
     */
    public function getCurrentCustomerAddresses()
    {
        $key = $this->customerAddressClass;
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $repository = $this->getCustomerAddressRepository();
        $result = $repository->getAddresses($this->aclHelper);

        $this->cache[$key] = $result;

        return $result;
    }

    /**
     * @return CustomerUserAddress[]
     */
    public function getCurrentCustomerUserAddresses()
    {
        $key = $this->customerUserAddressClass;
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $repository = $this->getCustomerUserAddressRepository();
        $result = $repository->getAddresses($this->aclHelper);

        $this->cache[$key] = $result;

        return $result;
    }

    /**
     * @return CustomerAddressRepository
     */
    protected function getCustomerAddressRepository()
    {
        return $this->registry->getManagerForClass($this->customerAddressClass)
            ->getRepository($this->customerAddressClass);
    }

    /**
     * @return CustomerUserAddressRepository
     */
    protected function getCustomerUserAddressRepository()
    {
        return $this->registry->getManagerForClass($this->customerUserAddressClass)
            ->getRepository($this->customerUserAddressClass);
    }
}
