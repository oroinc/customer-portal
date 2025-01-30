<?php

namespace Oro\Bundle\AddressValidationBundle\Controller\Frontend;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\AddressValidationResultHandler\AddressValidationResultHandlerInterface;
use Oro\Bundle\AddressValidationBundle\Form\Factory\AddressValidationAddressFormFactoryInterface;
use Oro\Bundle\AddressValidationBundle\Form\Type\Frontend\FrontendAddressValidationResultType;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base controller that renders the Address Validation dialog on storefront, handles its submit.
 */
abstract class AbstractAddressValidationController extends AbstractController
{
    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            AddressValidationResolverInterface::class,
            AddressValidationAddressFormFactoryInterface::class,
            AddressValidationResultHandlerInterface::class,
        ];
    }

    public function addressValidationAction(Request $request): Response|array
    {
        $addressForm = $this->createAddressForm($request);
        $this->handleAddressFormRequest($addressForm, $request);

        if (!$addressForm->isSubmitted() || !$addressForm->isValid()) {
            return $this->getWidgetEventResponse(false, 'fail', ['reason' => 'no_address']);
        }

        $originalAddress = $this->getOriginalAddress($addressForm, $request);
        $addressValidationResultForm = $this->createAddressValidationResultForm($originalAddress);

        $this
            ->getAddressValidationResultHandler()
            ->handleAddressValidationRequest($addressValidationResultForm, $request);

        if (!$addressValidationResultForm->isSubmitted() || !$addressValidationResultForm->isValid()) {
            return [
                'data' => [
                    'form' => $addressValidationResultForm->createView(),
                ],
            ];
        }

        return $this->getWidgetEventSuccessResponse($addressForm, $addressValidationResultForm, $request);
    }

    protected function createAddressForm(Request $request, ?AbstractAddress $address = null): FormInterface
    {
        return $this->container->get(AddressValidationAddressFormFactoryInterface::class)
            ->createAddressForm($request, $address);
    }

    protected function handleAddressFormRequest(FormInterface $addressForm, Request $request): void
    {
        $submittedData = $request->request->all()[$addressForm->getRoot()->getName()] ?? [];

        $addressForm->getRoot()->submit($submittedData, false);
    }

    protected function getOriginalAddress(FormInterface $addressForm, Request $request): AbstractAddress
    {
        /** @var AbstractAddress $originalAddress */
        $originalAddress = $addressForm->getData();

        return $originalAddress;
    }

    protected function createAddressValidationResultForm(AbstractAddress $originalAddress): FormInterface
    {
        $suggestedAddresses = $this->getSuggestedAddresses($originalAddress);

        return $this->createForm(
            FrontendAddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'csrf_protection' => false,
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );
    }

    /**
     * @param AbstractAddress $originalAddress
     *
     * @return array<ResolvedAddress>
     */
    protected function getSuggestedAddresses(AbstractAddress $originalAddress): array
    {
        return $this->container->get(AddressValidationResolverInterface::class)->resolve($originalAddress);
    }

    protected function getAddressValidationResultHandler(): AddressValidationResultHandlerInterface
    {
        return $this->container->get(AddressValidationResultHandlerInterface::class);
    }

    protected function getWidgetEventSuccessResponse(
        FormInterface $addressForm,
        FormInterface $addressValidationResultForm,
        Request $request
    ): JsonResponse {
        $selectedAddressForm = $this->createAddressForm(
            $request,
            $addressValidationResultForm->get('address')->getData()
        );
        $selectedAddressIndex = (string)$addressValidationResultForm->get('address')->getViewData();

        return $this->getWidgetEventResponse(true, 'success', [
            'selectedAddressIndex' => $selectedAddressIndex,
            'addressForm' => $this->renderView(
                '@OroAddressValidation/AddressValidation/addressForm.html.twig',
                ['form' => $selectedAddressForm]
            ),
        ]);
    }

    protected function getWidgetEventResponse(bool $success, string $eventName, array $eventArgs): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => $success,
                // Processed in _onJsonContentResponse. See in the 'oroui/js/widget/abstract-widget' for more.
                'widget' => [
                    'trigger' => [
                        [
                            'eventBroker' => 'widget',
                            'name' => $eventName,
                            'args' => [$eventArgs],
                        ],
                    ],
                ],
            ]
        );
    }
}
