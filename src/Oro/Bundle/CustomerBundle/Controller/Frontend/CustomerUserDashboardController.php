<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend;

use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Frontend controller for handling customer user dashboard actions.
 */
class CustomerUserDashboardController extends AbstractController
{
    #[Route(path: '/', name: 'oro_customer_frontend_customer_user_dashboard_index')]
    #[AclAncestor('oro_customer_frontend_customer_user_view')]
    #[Layout]
    public function indexAction(): array
    {
        return [];
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [];
    }
}
