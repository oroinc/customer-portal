<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Frontend controller for handling customer user dashboard actions.
 */
class CustomerUserDashboardController extends AbstractController
{
    #[Route(path: '/', name: 'oro_customer_frontend_customer_user_dashboard_index')]
    #[Layout]
    public function indexAction(): array
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        return [];
    }
}
