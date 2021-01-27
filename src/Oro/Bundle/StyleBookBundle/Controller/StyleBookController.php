<?php

namespace Oro\Bundle\StyleBookBundle\Controller;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Provides actions to show style book pages on frontend
 */
class StyleBookController extends AbstractController
{
    /**
     * @Layout(vars={"group"})
     * @Route("/", name="oro_stylebook")
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
     *     name="oro_stylebook_group",
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
        $isAllowStyleBook = $this->get('oro_stylebook.helper.access_helper')->isAllowStyleBook();
        if (!$isAllowStyleBook) {
            throw $this->createNotFoundException();
        }
    }
}
