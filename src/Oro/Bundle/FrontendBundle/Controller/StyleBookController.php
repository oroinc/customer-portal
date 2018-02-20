<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StyleBookController extends Controller
{
    /**
     * @Layout(vars={"group"})
     * @Route("/", name="oro_frontend_style_book")
     */
    public function indexAction()
    {
        $this->checkAccess();
        return [
            'group' => null,
        ];
    }

    /**
     * @Layout(vars={"group"})
     * @Route(
     *     "/{group}/",
     *     name="oro_frontend_style_book_group",
     *     requirements={"group"="\w+"}
     * )
     * @param string $group
     *
     * @return array
     */
    public function groupAction($group)
    {
        $this->checkAccess();
        return [
            'group' => $group,
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function checkAccess()
    {
        $isDebug = $this->getParameter('kernel.debug');
        if (!$isDebug) {
            throw $this->createNotFoundException();
        }
    }
}
