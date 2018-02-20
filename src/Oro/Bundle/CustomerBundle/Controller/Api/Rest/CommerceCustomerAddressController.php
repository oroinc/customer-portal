<?php

namespace Oro\Bundle\CustomerBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @NamePrefix("oro_api_customer_")
 */
class CommerceCustomerAddressController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET address
     *
     * @param int $entityId
     * @param string $addressId
     *
     * @ApiDoc(
     *      description="Get customer address",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_address_view")
     * @return Response
     */
    public function getAction($entityId, $addressId)
    {
        /** @var Customer $customer */
        $customer = $this->getCustomerManager()->find($entityId);

        /** @var CustomerAddress $address */
        $address = $this->getManager()->find($addressId);

        $addressData = null;
        if ($address && $customer->getAddresses()->contains($address)) {
            $addressData = $this->getPreparedItem($address);
        }
        $responseData = $addressData ? json_encode($addressData) : '';
        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_address_view")
     * @param int $entityId
     *
     * @return JsonResponse
     */
    public function cgetAction($entityId)
    {
        /** @var Customer $customer */
        $customer = $this->getCustomerManager()->find($entityId);
        $result = [];

        if ($customer) {
            $items = $this->getCustomerAddresses($customer);

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse($result, $customer ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_address_remove")
     * @param int $entityId
     * @param int $addressId
     *
     * @return Response
     */
    public function deleteAction($entityId, $addressId)
    {
        /** @var CustomerAddress $address */
        $address = $this->getManager()->find($addressId);
        /** @var Customer $customer */
        $customer = $this->getCustomerManager()->find($entityId);
        if ($this->get('oro_customer.provider.frontend.address')->isCurrentCustomerAddressesContain($address)) {
            $customer->removeAddress($address);
            return $this->handleDeleteRequest($addressId);
        } else {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }
    }

    /**
     * REST GET address by type
     *
     * @param int $entityId
     * @param string $typeName
     *
     * @ApiDoc(
     *      description="Get customer address by type",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_address_view")
     * @return Response
     */
    public function getByTypeAction($entityId, $typeName)
    {
        /** @var Customer $customer */
        $customer = $this->getCustomerManager()->find($entityId);

        if ($customer) {
            $address = $customer->getAddressByTypeName($typeName);
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * REST GET primary address
     *
     * @param int $entityId
     *
     * @ApiDoc(
     *      description="Get customer primary address",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_address_view")
     * @return Response
     */
    public function getPrimaryAction($entityId)
    {
        /** @var Customer $customer */
        $customer = $this->getCustomerManager()->find($entityId);

        if ($customer) {
            $address = $customer->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * @return \Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager
     */
    protected function getCustomerManager()
    {
        return $this->get('oro_customer.manager.customer.api.attribute');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_customer.customer_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        // convert addresses to plain array
        $addressTypesData = [];

        /** @var $addressType AddressType */
        foreach ($entity->getTypes() as $addressType) {
            $addressTypesData[] = parent::getPreparedItem($addressType);
        }

        $addressDefaultsData = [];

        /** @var  $defaultType AddressType */
        foreach ($entity->getDefaults() as $defaultType) {
            $addressDefaultsData[] = parent::getPreparedItem($defaultType);
        }

        $result                = parent::getPreparedItem($entity);
        $result['types']       = $addressTypesData;
        $result['defaults']    = $addressDefaultsData;
        $result['countryIso2'] = $entity->getCountryIso2();
        $result['countryIso3'] = $entity->getCountryIso2();
        $result['regionCode']  = $entity->getRegionCode();
        $result['combinedCode'] = $entity->getRegion() ? $entity->getRegion()->getCombinedCode() : null;
        $result['country'] = $entity->getCountryName();

        unset($result['frontendOwner']);

        return $result;
    }

    /**
     * @param Customer $customer
     * @return array
     */
    protected function getCustomerAddresses(Customer $customer)
    {
        return $customer->getAddresses()->toArray();
    }
}
