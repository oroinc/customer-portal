<?php

namespace Oro\Bundle\StyleBookBundle\Controller;

use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\StyleBookBundle\Helper\AccessHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Provides actions to show style book pages on frontend
 */
class StyleBookController extends AbstractController
{
    #[Route(path: '/', name: 'oro_stylebook')]
    #[Layout(vars: ['group'])]
    public function indexAction()
    {
        $this->checkAccess();
        return [
            'group' => null,
        ];
    }

    /**
     * @param string $group
     * @return array
     */
    #[Route(path: '/{group}/', name: 'oro_stylebook_group', requirements: ['group' => '\w+'])]
    #[Layout(vars: ['group'])]
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
        $isAllowStyleBook = $this->container->get(AccessHelper::class)->isAllowStyleBook();
        if (!$isAllowStyleBook) {
            throw $this->createNotFoundException();
        }
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                AccessHelper::class,
            ]
        );
    }
}
