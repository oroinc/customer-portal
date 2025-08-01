<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles Customer user profile view and update actions
 */
class CustomerUserProfileController extends AbstractController
{
    /**
     * @return array
     */
    #[Route(path: '/', name: 'oro_customer_frontend_customer_user_profile')]
    #[Layout]
    public function profileAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        return [
            'data' => [
                'entity' => $this->getUser()
            ]
        ];
    }

    /**
     * Edit customer user form
     *
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    #[Route(path: '/update', name: 'oro_customer_frontend_customer_user_profile_update')]
    #[Layout]
    #[AclAncestor('oro_customer_frontend_update_own_profile')]
    public function updateAction(Request $request)
    {
        $customerUser = $this->getUser();
        $form = $this->container->get(FrontendCustomerUserFormProvider::class)
            ->getProfileForm($customerUser);

        $handler = $this->container->get(FrontendCustomerUserHandler::class);
        $saveMessage = $this->container->get(TranslatorInterface::class)
            ->trans('oro.customer.controller.customeruser.profile_updated.message');
        $resultHandler = $this->container->get(UpdateHandlerFacade::class)->update(
            $customerUser,
            $form,
            $saveMessage,
            $request,
            $handler
        );

        if ($resultHandler instanceof Response) {
            return $resultHandler;
        }

        $fallbackUrl = $this->container->get('router')->generate('oro_customer_frontend_customer_user_profile');

        return [
            'data' => [
                'backToUrl' => $this->container->get(SameSiteUrlHelper::class)
                    ->getSameSiteReferer($request, $fallbackUrl),
                'entity' => $customerUser,
            ]
        ];
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                UpdateHandlerFacade::class,
                TranslatorInterface::class,
                FrontendCustomerUserFormProvider::class,
                FrontendCustomerUserHandler::class,
                SameSiteUrlHelper::class,
            ]
        );
    }
}
