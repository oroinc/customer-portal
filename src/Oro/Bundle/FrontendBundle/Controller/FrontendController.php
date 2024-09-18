<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Oro\Bundle\FrontendBundle\Provider\HomePageProviderInterface;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default storefront controller.
 */
class FrontendController extends AbstractController
{
    #[Route(path: '/', name: 'oro_frontend_root')]
    #[Layout]
    public function indexAction(): array
    {
        return [
            'data' => [
                'page' => $this->container->get(HomePageProviderInterface::class)->getHomePage()
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            HomePageProviderInterface::class
        ]);
    }
}
