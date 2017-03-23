<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oro\Bundle\LayoutBundle\Annotation\Layout;

class StyleBookController extends Controller
{
    /**
     * @Layout
     * @Route(
     *     "/style-book/{group}",
     *     name="oro_frontend_style_book",
     *     defaults={"group" = null},
     *     requirements={"group"=".+"}
     * )
     * @throws NotFoundHttpException
     */
    public function indexAction($group = null)
    {
        $isDebug = $this->getParameter('kernel.debug');
        if (!$isDebug) {
            throw $this->createNotFoundException();
        }

        return [
            'action' => (string)$group,
        ];
    }
}
