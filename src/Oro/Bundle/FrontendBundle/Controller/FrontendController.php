<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\LayoutBundle\Annotation\Layout;

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

    /**
     * @Route("/exception/{code}/{text}", name="oro_frontend_exception", requirements={"code"="\d+"})
     * @param string $code
     * @param string $text
     * @throws AccessDeniedException
     */
    public function exceptionAction($code, $text)
    {
        // This action is left here for the BC reason. Please don't use it on production.
        throw $this->createAccessDeniedException();
    }
}
