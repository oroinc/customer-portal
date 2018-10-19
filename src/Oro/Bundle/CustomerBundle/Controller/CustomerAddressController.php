<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CustomerAddressController extends Controller
{
    /**
     * @Route("/address-book/{id}", name="oro_customer_address_book", requirements={"id"="\d+"})
     * @Template("OroCustomerBundle:Address/widget:addressBook.html.twig")
     * @AclAncestor("oro_customer_customer_address_view")
     *
     * @param Request $request
     * @param Customer $customer
     * @return array
     */
    public function addressBookAction(Request $request, Customer $customer)
    {
        return [
            'entity' => $customer,
            'options' => $this->getAddressBookOptions($request, $customer)
        ];
    }

    /**
     * @Route(
     *      "/{entityId}/address-create",
     *      name="oro_customer_address_create",
     *      requirements={"entityId"="\d+"}
     * )
     * @Template("OroCustomerBundle:Address/widget:update.html.twig")
     * @AclAncestor("oro_customer_customer_address_create")
     * @ParamConverter("customer", options={"id" = "entityId"})
     *
     * @param Request $request
     * @param Customer $customer
     * @return array
     */
    public function createAction(Request $request, Customer $customer)
    {
        return $this->update($request, $customer, new CustomerAddress());
    }

    /**
     * @Route(
     *      "/{entityId}/address-update/{id}",
     *      name="oro_customer_address_update",
     *      requirements={"entityId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template("OroCustomerBundle:Address/widget:update.html.twig")
     * @AclAncestor("oro_customer_customer_address_update")
     * @ParamConverter("customer", options={"id" = "entityId"})
     *
     * @param Request         $request
     * @param Customer        $customer
     * @param CustomerAddress $address
     * @return array
     */
    public function updateAction(Request $request, Customer $customer, CustomerAddress $address)
    {
        return $this->update($request, $customer, $address);
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @param CustomerAddress $address
     * @return array
     * @throws BadRequestHttpException
     */
    protected function update(Request $request, Customer $customer, CustomerAddress $address)
    {
        $responseData = [
            'saved' => false,
            'entity' => $customer
        ];

        if ($request->isMethod('GET') && !$address->getId() && !$customer->getAddresses()->count()) {
            $address->setPrimary(true);
        }

        if (!$address->getFrontendOwner()) {
            $customer->addAddress($address);
        } elseif ($address->getFrontendOwner()->getId() !== $customer->getId()) {
            throw new BadRequestHttpException('Address must belong to Customer');
        }

        $form = $this->createForm(CustomerTypedAddressType::class, $address);

        $handler = new AddressHandler(
            $form,
            $this->get('request_stack'),
            $this->getDoctrine()->getManagerForClass(
                $this->container->getParameter('oro_customer.entity.customer_address.class')
            )
        );

        if ($handler->process($address)) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();
        $responseData['routes'] = [
            'create' => 'oro_customer_address_create',
            'update' => 'oro_customer_address_update'
        ];
        return $responseData;
    }

    /**
     * @param Request $request
     * @param Customer $entity
     * @return array
     */
    protected function getAddressBookOptions(Request $request, Customer $entity)
    {
        $addressListUrl = $this->generateUrl('oro_api_customer_get_commercecustomer_addresses', [
            'entityId' => $entity->getId()
        ]);

        $addressCreateUrl = $this->generateUrl('oro_customer_address_create', [
            'entityId' => $entity->getId()
        ]);

        $currentAddresses = $this->get('fragment.handler')->render($addressListUrl);

        return [
            'wid'                    => $request->get('_wid'),
            'entityId'               => $entity->getId(),
            'addressListUrl'         => $addressListUrl,
            'addressCreateUrl'       => $addressCreateUrl,
            'addressUpdateRouteName' => 'oro_customer_address_update',
            'currentAddresses'       => $currentAddresses,
            'addressDeleteRouteName' => 'oro_api_customer_delete_commercecustomer_address',
            'acl'                    => $this->getAclResources()
        ];
    }

    /**
     * @return array
     */
    private function getAclResources()
    {
        return [
            'addressEdit' => 'oro_customer_customer_address_update',
            'addressCreate' => 'oro_customer_customer_address_create',
            'addressRemove' => 'oro_customer_customer_address_remove',
        ];
    }
}
