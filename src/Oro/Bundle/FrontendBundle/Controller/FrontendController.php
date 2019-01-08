<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Default frontend controller.
 */
class FrontendController extends Controller
{
    /**
     * @Layout
     * @Route("/", name="oro_frontend_root")
     */
    public function indexAction()
    {
        return [];
    }
}
