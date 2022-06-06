<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Util\SameSiteUrlHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles Customer user profile view and update actions
 */
class CustomerUserProfileController extends AbstractController
{
    /**
     * @Route("/", name="oro_customer_frontend_customer_user_profile")
     * @Layout
     *
     * @return array
     */
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
     * @Route("/update", name="oro_customer_frontend_customer_user_profile_update")
     * @Layout()
     * @AclAncestor("oro_customer_frontend_update_own_profile")
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function updateAction(Request $request)
    {
        $customerUser = $this->getUser();
        $form = $this->get(FrontendCustomerUserFormProvider::class)
            ->getProfileForm($customerUser);

        $handler = $this->get(FrontendCustomerUserHandler::class);
        $saveMessage = $this->get(TranslatorInterface::class)
            ->trans('oro.customer.controller.customeruser.profile_updated.message');
        $resultHandler = $this->get(UpdateHandlerFacade::class)->update(
            $customerUser,
            $form,
            $saveMessage,
            $request,
            $handler
        );

        if ($resultHandler instanceof Response) {
            return $resultHandler;
        }

        $fallbackUrl = $this->get('router')->generate('oro_customer_frontend_customer_user_profile');

        return [
            'data' => [
                'backToUrl' => $this->get(SameSiteUrlHelper::class)->getSameSiteReferer($request, $fallbackUrl),
                'entity' => $customerUser,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
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
