<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerAddressFormProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for customer address entity.
 */
class CustomerAddressController extends AbstractController
{
    #[Route(
        path: '/{entityId}/create',
        name: 'oro_customer_frontend_customer_address_create',
        requirements: ['entityId' => '\d+']
    )]
    #[Layout]
    #[ParamConverter('customer', options: ['id' => 'entityId'])]
    #[AclAncestor('oro_customer_frontend_customer_address_create')]
    public function createAction(Customer $customer, Request $request): array|RedirectResponse
    {
        return $this->update($customer, new CustomerAddress(), $request);
    }

    #[Route(
        path: '/{entityId}/update/{id}',
        name: 'oro_customer_frontend_customer_address_update',
        requirements: ['entityId' => '\d+', 'id' => '\d+']
    )]
    #[Layout]
    #[ParamConverter('customer', options: ['id' => 'entityId'])]
    #[ParamConverter('customerAddress', options: ['id' => 'id'])]
    #[AclAncestor('oro_customer_frontend_customer_address_update')]
    public function updateAction(
        Customer $customer,
        CustomerAddress $customerAddress,
        Request $request
    ): array|RedirectResponse {
        return $this->update($customer, $customerAddress, $request);
    }

    private function update(
        Customer $customer,
        CustomerAddress $customerAddress,
        Request $request
    ): array|RedirectResponse {
        $this->prepareEntities($customer, $customerAddress, $request);

        $form = $this->container->get(FrontendCustomerAddressFormProvider::class)
            ->getAddressForm($customerAddress, $customer);

        $manager = $this->container->get('doctrine')->getManagerForClass(CustomerAddress::class);

        $handler = new AddressHandler($manager);

        $result = $this->container->get(UpdateHandlerFacade::class)->update(
            $form->getData(),
            $form,
            $this->container->get(TranslatorInterface::class)
                ->trans('oro.customer.controller.customeraddress.saved.message'),
            $request,
            $handler,
            function (CustomerAddress $customerAddress, FormInterface $form, Request $request) {
                return [
                    'backToUrl' => $this->container->get(SameSiteUrlHelper::class)
                        ->getSameSiteReferer($request, $request->getUri()),
                    'input_action' => \json_encode([
                        'route' => 'oro_customer_frontend_customer_user_address_index',
                        'params' => []
                    ])
                ];
            }
        );

        if ($result instanceof Response) {
            return $result;
        }

        return [
            'data' => array_merge($result, ['customer' => $customer])
        ];
    }

    private function prepareEntities(Customer $customer, CustomerAddress $customerAddress, Request $request): void
    {
        if ($request->getMethod() === 'GET' && !$customerAddress->getId()) {
            $customerAddress->setFirstName($this->getUser()->getFirstName());
            $customerAddress->setLastName($this->getUser()->getLastName());
            if (!$customer->getAddresses()->count()) {
                $customerAddress->setPrimary(true);
            }
        }

        if (!$customerAddress->getFrontendOwner()) {
            $customer->addAddress($customerAddress);
        } elseif ($customerAddress->getFrontendOwner()->getId() !== $customer->getId()) {
            throw new BadRequestHttpException('Address must belong to Customer');
        }
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                FrontendCustomerAddressFormProvider::class,
                SameSiteUrlHelper::class,
                UpdateHandlerFacade::class,
                'doctrine' => ManagerRegistry::class
            ]
        );
    }
}
