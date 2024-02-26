<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default frontend controller.
 */
class FrontendController extends AbstractController
{
    #[Route(path: '/', name: 'oro_frontend_root')]
    #[Layout]
    public function indexAction()
    {
        return [];
    }
}
