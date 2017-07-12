<?php

namespace Oro\Bundle\CustomerBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @NamePrefix("oro_api_customer_")
 */
class CustomerUserAddressController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET address
     *
     * @param int $entityId
     * @param string $addressId
     *
     * @ApiDoc(
     *      description="Get customer user address",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_user_address_view")
     * @return Response
     */
    public function getAction($entityId, $addressId)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);

        /** @var CustomerAddress $address */
        $address = $this->getManager()->find($addressId);

        $addressData = null;
        if ($address && $customerUser->getAddresses()->contains($address)) {
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
     * @AclAncestor("oro_customer_customer_user_address_view")
     * @param int $entityId
     * @param Request $request
     * @return JsonResponse
     */
    public function cgetAction($entityId, Request $request)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);
        $result  = [];

        if ($customerUser) {
            $addresses = $customerUser->getAddresses();
            if ($request->query->get('default_only')) {
                /** @var AbstractDefaultTypedAddress $address */
                foreach ($addresses as $address) {
                    if ($address->isPrimary() || $address->getDefaults()->count()) {
                        $result[] = $this->getPreparedItem($address);
                    }
                }
            } else {
                foreach ($addresses as $address) {
                    $result[] = $this->getPreparedItem($address);
                }
            }
        }

        return new JsonResponse($result, $customerUser ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_user_address_remove")
     * @param int $entityId
     * @param int $addressId
     *
     * @return Response
     */
    public function deleteAction($entityId, $addressId)
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getManager()->find($addressId);
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);
        if ($customerUser->getAddresses()->contains($address)) {
            $customerUser->removeAddress($address);
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
     *      description="Get customer user address by type",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_user_address_view")
     * @return Response
     */
    public function getByTypeAction($entityId, $typeName)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);

        if ($customerUser) {
            $address = $customerUser->getAddressByTypeName($typeName);
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
     *      description="Get customer user primary address",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_user_address_view")
     * @return Response
     */
    public function getPrimaryAction($entityId)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);

        if ($customerUser) {
            $address = $customerUser->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * @return \Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager
     */
    protected function getCustomerUserManager()
    {
        return $this->get('oro_customer.customer_user.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_customer.customer_user_address.manager.api');
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
}
