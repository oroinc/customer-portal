<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerAddressRepository;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserAddressRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FrontendAddressProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @var string
     */
    protected $customerAddressClass;

    /**
     * @var string
     */
    protected $customerUserAddressClass;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @param ManagerRegistry $registry
     * @param AclHelper $aclHelper
     * @param string $customerAddressClass
     * @param string $customerUserAddressClass
     */
    public function __construct(
        ManagerRegistry $registry,
        AclHelper $aclHelper,
        $customerAddressClass,
        $customerUserAddressClass
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;

        $this->customerAddressClass = $customerAddressClass;
        $this->customerUserAddressClass = $customerUserAddressClass;
    }

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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

    /**
     * @param CustomerAddress $customerAddress
     * @return bool
     */
    public function isCurrentCustomerAddressesContain(CustomerAddress $customerAddress)
    {
        return $this->authorizationChecker->isGranted('VIEW', $customerAddress);
    }

    /**
     * @param CustomerUserAddress $customerUserAddress
     * @return bool
     */
    public function isCurrentCustomerUserAddressesContain(CustomerUserAddress $customerUserAddress)
    {
        return $this->authorizationChecker->isGranted('VIEW', $customerUserAddress);
    }
}
