<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserAddressFormProvider;
use Oro\Bundle\CustomerBundle\Provider\FrontendAddressProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for customer user address entity.
 */
class CustomerUserAddressController extends AbstractController
{
    /**
     * @Route("/", name="oro_customer_frontend_customer_user_address_index")
     * @Layout(vars={"entity_class", "customer_address_count", "customer_user_address_count"})
     *
     * @return array
     */
    public function indexAction()
    {
        if (!$this->isGranted('oro_customer_frontend_customer_address_view')
            && !$this->isGranted('oro_customer_frontend_customer_user_address_view')
        ) {
            throw new AccessDeniedException();
        }

        $addressProvider = $this->get(FrontendAddressProvider::class);

        return [
            'entity_class' => CustomerUserAddress::class,
            'customer_user_address_count' => count($addressProvider->getCurrentCustomerUserAddresses()),
            'customer_address_count' => count($addressProvider->getCurrentCustomerAddresses()),
            'data' => [
                'entity' => $this->getUser()
            ]
        ];
    }

    /**
     * @Route(
     *     "/{entityId}/address-create",
     *     name="oro_customer_frontend_customer_user_address_create",
     *     requirements={"entityId":"\d+"}
     * )
     * @AclAncestor("oro_customer_frontend_customer_user_address_create")
     * @Layout
     *
     * @ParamConverter("customerUser", options={"id" = "entityId"})
     *
     * @param CustomerUser $customerUser
     * @param Request $request
     * @return array
     */
    public function createAction(CustomerUser $customerUser, Request $request)
    {
        if (!$this->isGranted('oro_customer_frontend_customer_user_view', $customerUser)) {
            throw new AccessDeniedException();
        }

        return $this->update($customerUser, new CustomerUserAddress(), $request);
    }

    /**
     * @Route(
     *     "/{entityId}/address/{id}/update",
     *     name="oro_customer_frontend_customer_user_address_update",
     *     requirements={"entityId":"\d+", "id":"\d+"}
     * )
     * @AclAncestor("oro_customer_frontend_customer_user_address_update")
     * @Layout
     *
     * @ParamConverter("customerUser", options={"id" = "entityId"})
     *
     * @param CustomerUser $customerUser
     * @param CustomerUserAddress $customerAddress
     * @param Request $request
     * @return array
     */
    public function updateAction(CustomerUser $customerUser, CustomerUserAddress $customerAddress, Request $request)
    {
        return $this->update($customerUser, $customerAddress, $request);
    }

    /**
     * @param CustomerUser $customerUser
     * @param CustomerUserAddress $customerAddress
     * @param Request $request
     * @return array
     */
    private function update(CustomerUser $customerUser, CustomerUserAddress $customerAddress, Request $request)
    {
        $this->prepareEntities($customerUser, $customerAddress, $request);

        $form = $this->get(FrontendCustomerUserAddressFormProvider::class)
            ->getAddressForm($customerAddress, $customerUser);

        $currentUser = $this->getUser();

        $manager = $this->getDoctrine()->getManagerForClass(CustomerUserAddress::class);

        $handler = new AddressHandler($form, $this->get('request_stack'), $manager);

        $result = $this->get(UpdateHandler::class)->handleUpdate(
            $form->getData(),
            $form,
            function (CustomerUserAddress $customerAddress) use ($customerUser) {
                return [
                    'route' => 'oro_customer_frontend_customer_user_address_update',
                    'parameters' => ['id' => $customerAddress->getId(), 'entityId' => $customerUser->getId()],
                ];
            },
            function (CustomerUserAddress $customerAddress) use ($customerUser, $currentUser) {
                if ($currentUser instanceof CustomerUser && $currentUser->getId() === $customerUser->getId()) {
                    return ['route' => 'oro_customer_frontend_customer_user_address_index'];
                } else {
                    return [
                        'route' => 'oro_customer_frontend_customer_user_view',
                        'parameters' => ['id' => $customerUser->getId()],
                    ];
                }
            },
            $this->get(TranslatorInterface::class)->trans('oro.customer.controller.customeruseraddress.saved.message'),
            $handler,
            function (CustomerUserAddress $customerAddress, FormInterface $form, Request $request) {
                return [
                    'backToUrl' => $this->get(SameSiteUrlHelper::class)
                        ->getSameSiteReferer($request, $request->getUri()),
                ];
            }
        );

        if ($result instanceof Response) {
            return $result;
        }

        return [
            'data' => array_merge($result, ['customerUser' => $customerUser])
        ];
    }

    private function prepareEntities(
        CustomerUser $customerUser,
        CustomerUserAddress $customerUserAddress,
        Request $request
    ) {
        if ($request->getMethod() === 'GET' && !$customerUserAddress->getId()) {
            $customerUserAddress->setFirstName($customerUser->getFirstName());
            $customerUserAddress->setLastName($customerUser->getLastName());
            if (!$customerUser->getAddresses()->count()) {
                $customerUserAddress->setPrimary(true);
            }
        }

        if (!$customerUserAddress->getFrontendOwner()) {
            $customerUser->addAddress($customerUserAddress);
        } elseif ($customerUserAddress->getFrontendOwner()->getId() !== $customerUser->getId()) {
            throw new BadRequestHttpException('Address must belong to CustomerUser');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                UpdateHandler::class,
                TranslatorInterface::class,
                FrontendAddressProvider::class,
                FrontendCustomerUserAddressFormProvider::class,
                SameSiteUrlHelper::class,
            ]
        );
    }
}
