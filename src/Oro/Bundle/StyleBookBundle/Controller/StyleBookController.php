<?php

namespace Oro\Bundle\StyleBookBundle\Controller;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\StyleBookBundle\Helper\AccessHelper;
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
        $isAllowStyleBook = $this->get(AccessHelper::class)->isAllowStyleBook();
        if (!$isAllowStyleBook) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                AccessHelper::class,
            ]
        );
    }
}
