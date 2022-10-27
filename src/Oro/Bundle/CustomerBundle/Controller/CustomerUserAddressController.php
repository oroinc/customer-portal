<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserTypedAddressType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles CRUD of CustomerUserAddress entity.
 */
class CustomerUserAddressController extends AbstractController
{
    /**
     * @Route("/address-book/{id}", name="oro_customer_customer_user_address_book", requirements={"id"="\d+"})
     * @Template("@OroCustomer/Address/widget/addressBook.html.twig")
     * @AclAncestor("oro_customer_customer_user_address_view")
     */
    public function addressBookAction(Request $request, CustomerUser $customerUser): array
    {
        return [
            'entity' => $customerUser,
            'options' => $this->getAddressBookOptions($request, $customerUser)
        ];
    }

    /**
     * @Route(
     *      "/{entityId}/address-create",
     *      name="oro_customer_customer_user_address_create",
     *      requirements={"customerUserId"="\d+"}
     * )
     * @Template("@OroCustomer/Address/widget/update.html.twig")
     * @AclAncestor("oro_customer_customer_user_address_create")
     * @ParamConverter("customerUser", options={"id" = "entityId"})
     */
    public function createAction(Request $request, CustomerUser $customerUser): array
    {
        return $this->update($request, $customerUser, new CustomerUserAddress());
    }

    /**
     * @Route(
     *      "/{entityId}/address-update/{id}",
     *      name="oro_customer_customer_user_address_update",
     *      requirements={"customerUserId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template("@OroCustomer/Address/widget/update.html.twig")
     * @AclAncestor("oro_customer_customer_user_address_update")
     * @ParamConverter("customerUser", options={"id" = "entityId"})
     */
    public function updateAction(Request $request, CustomerUser $customerUser, CustomerUserAddress $address): array
    {
        return $this->update($request, $customerUser, $address);
    }

    /**
     * @throws BadRequestHttpException
     */
    protected function update(Request $request, CustomerUser $customerUser, CustomerUserAddress $address): array
    {
        $responseData = [
            'saved' => false,
            'entity' => $customerUser
        ];

        if ($request->getMethod() === 'GET' && !$address->getId()) {
            $address->setFirstName($customerUser->getFirstName());
            $address->setLastName($customerUser->getLastName());
            if (!$customerUser->getAddresses()->count()) {
                $address->setPrimary(true);
            }
        }

        if (!$address->getFrontendOwner()) {
            $customerUser->addAddress($address);
        } elseif ($address->getFrontendOwner()->getId() !== $customerUser->getId()) {
            throw new BadRequestHttpException('Address must belong to CustomerUser');
        }

        $form = $this->createForm(CustomerUserTypedAddressType::class, $address);

        $manager = $this->getDoctrine()->getManagerForClass(CustomerUserAddress::class);

        $handler = new AddressHandler($manager);

        if ($handler->process($address, $form, $request)) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();
        $responseData['routes'] = [
            'create' => 'oro_customer_customer_user_address_create',
            'update' => 'oro_customer_customer_user_address_update'
        ];
        return $responseData;
    }

    protected function getAddressBookOptions(Request $request, CustomerUser $entity): array
    {
        $addressListUrl = $this->generateUrl('oro_api_customer_get_customeruser_addresses', [
            'entityId' => $entity->getId()
        ]);

        $addressCreateUrl = $this->generateUrl('oro_customer_customer_user_address_create', [
            'entityId' => $entity->getId()
        ]);

        $currentAddresses = $this->get('fragment.handler')->render($addressListUrl);

        return [
            'wid'                    => $request->get('_wid'),
            'entityId'               => $entity->getId(),
            'addressListUrl'         => $addressListUrl,
            'addressCreateUrl'       => $addressCreateUrl,
            'addressUpdateRouteName' => 'oro_customer_customer_user_address_update',
            'currentAddresses'       => $currentAddresses,
            'addressDeleteRouteName' => 'oro_api_customer_delete_customeruser_address',
            'acl'                    => $this->getAclResources()
        ];
    }

    private function getAclResources(): array
    {
        return [
            'addressEdit' => 'oro_customer_customer_user_address_update',
            'addressCreate' => 'oro_customer_customer_user_address_create',
            'addressRemove' => 'oro_customer_customer_user_address_remove',
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
