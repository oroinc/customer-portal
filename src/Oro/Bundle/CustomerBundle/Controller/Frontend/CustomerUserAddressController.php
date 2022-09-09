<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserAddressFormProvider;
use Oro\Bundle\CustomerBundle\Provider\FrontendAddressProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     */
    public function indexAction(): array
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
     * @ParamConverter("customerUser", options={"id" = "entityId"})
     */
    public function createAction(CustomerUser $customerUser, Request $request): array|RedirectResponse
    {
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
     * @ParamConverter("customerUser", options={"id" = "entityId"})
     */
    public function updateAction(
        CustomerUser $customerUser,
        CustomerUserAddress $customerAddress,
        Request $request
    ): array|RedirectResponse {
        return $this->update($customerUser, $customerAddress, $request);
    }

    private function resolveInputAction(CustomerUser $customerUser): string
    {
        $currentUser = $this->getUser();

        if ($currentUser instanceof CustomerUser && $currentUser->getId() === $customerUser->getId()) {
            return \json_encode(['route' => 'oro_customer_frontend_customer_user_address_index']);
        }

        return \json_encode([
            'route' => 'oro_customer_frontend_customer_user_view',
            'params' => ['id' => $customerUser->getId()],
        ]);
    }

    private function update(
        CustomerUser $customerUser,
        CustomerUserAddress $customerAddress,
        Request $request
    ): array|RedirectResponse {
        $this->prepareEntities($customerUser, $customerAddress, $request);

        $form = $this->get(FrontendCustomerUserAddressFormProvider::class)
            ->getAddressForm($customerAddress, $customerUser);

        $manager = $this->getDoctrine()->getManagerForClass(CustomerUserAddress::class);

        $handler = new AddressHandler($manager);

        $result = $this->get(UpdateHandlerFacade::class)->update(
            $form->getData(),
            $form,
            $this->get(TranslatorInterface::class)->trans('oro.customer.controller.customeruseraddress.saved.message'),
            $request,
            $handler,
            function (CustomerUserAddress $customerAddress, FormInterface $form, Request $request) use ($customerUser) {
                return [
                    'backToUrl' => $this->get(SameSiteUrlHelper::class)
                        ->getSameSiteReferer($request, $request->getUri()),
                    'input_action' => $this->resolveInputAction($customerUser)
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
    ): void {
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
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                FrontendAddressProvider::class,
                FrontendCustomerUserAddressFormProvider::class,
                SameSiteUrlHelper::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
