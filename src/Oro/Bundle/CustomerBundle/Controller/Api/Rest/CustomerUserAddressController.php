<?php

namespace Oro\Bundle\CustomerBundle\Controller\Api\Rest;

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
 * REST API controller for customer user address entity.
 */
class CustomerUserAddressController extends RestController
{
    /**
     * REST GET address
     *
     * @param int $entityId
     * @param int $addressId
     *
     * @ApiDoc(
     *      description="Get customer user address",
     *      resource=true
     * )
     * @AclAncestor("oro_customer_customer_user_address_view")
     * @return Response
     */
    public function getAction(int $entityId, int $addressId)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);
        $this->checkAccess($customerUser);

        /** @var CustomerAddress $address */
        $address = $this->getManager()->find($addressId);
        if (!$this->isGranted('VIEW', $address)) {
            throw $this->createAccessDeniedException();
        }

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
    public function cgetAction(int $entityId, Request $request)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);
        $this->checkAccess($customerUser);

        $result  = [];
        if ($customerUser) {
            $addresses = $this->getCustomerUserAddresses($customerUser);

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
    public function deleteAction(int $entityId, int $addressId)
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getManager()->find($addressId);
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getCustomerUserManager()->find($entityId);
        if ($this->isGranted('DELETE', $address)) {
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
    public function getByTypeAction(int $entityId, $typeName)
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
    public function getPrimaryAction(int $entityId)
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
     * @param CustomerUserAddress $entity
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
        $result['ownerId'] = $entity->getFrontendOwner() ? $entity->getFrontendOwner()->getId() : null;

        unset($result['frontendOwner']);

        return $result;
    }

    /**
     * @param CustomerUser $customerUser
     * @return array
     */
    protected function getCustomerUserAddresses(CustomerUser $customerUser)
    {
        $dql = $this->getDoctrine()->getRepository(CustomerUserAddress::class)
            ->createQueryBuilder('address')
            ->select('address')
            ->where('address.frontendOwner = :frontendOwner')
            ->setParameter('frontendOwner', $customerUser);

        return $this->get('oro_security.acl_helper')->apply($dql)->getResult();
    }

    protected function checkAccess($entity)
    {
        if (!$this->isGranted('VIEW', $entity)) {
            throw $this->createAccessDeniedException();
        }
    }
}
