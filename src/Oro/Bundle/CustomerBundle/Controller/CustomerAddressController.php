<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerTypedAddressType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles CRUD of CustomerAddress entity.
 */
class CustomerAddressController extends AbstractController
{
    /**
     * @Route("/address-book/{id}", name="oro_customer_address_book", requirements={"id"="\d+"})
     * @Template("@OroCustomer/Address/widget/addressBook.html.twig")
     * @AclAncestor("oro_customer_customer_address_view")
     */
    public function addressBookAction(Request $request, Customer $customer): array
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
     * @Template("@OroCustomer/Address/widget/update.html.twig")
     * @AclAncestor("oro_customer_customer_address_create")
     * @ParamConverter("customer", options={"id" = "entityId"})
     */
    public function createAction(Request $request, Customer $customer): array
    {
        return $this->update($request, $customer, new CustomerAddress());
    }

    /**
     * @Route(
     *      "/{entityId}/address-update/{id}",
     *      name="oro_customer_address_update",
     *      requirements={"entityId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template("@OroCustomer/Address/widget/update.html.twig")
     * @AclAncestor("oro_customer_customer_address_update")
     * @ParamConverter("customer", options={"id" = "entityId"})
     */
    public function updateAction(Request $request, Customer $customer, CustomerAddress $address): array
    {
        return $this->update($request, $customer, $address);
    }

    /**
     * @throws BadRequestHttpException
     */
    protected function update(Request $request, Customer $customer, CustomerAddress $address): array
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

        $handler = new AddressHandler($this->getDoctrine()->getManagerForClass(CustomerAddress::class));

        if ($handler->process($address, $form, $request)) {
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

    protected function getAddressBookOptions(Request $request, Customer $entity): array
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

    private function getAclResources(): array
    {
        return [
            'addressEdit' => 'oro_customer_customer_address_update',
            'addressCreate' => 'oro_customer_customer_address_create',
            'addressRemove' => 'oro_customer_customer_address_remove',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'fragment.handler' => FragmentHandler::class,
            ]
        );
    }
}
