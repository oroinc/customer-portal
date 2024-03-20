<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Oro\Bundle\CMSBundle\Provider\HomeLandingPageProvider;
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
    public function indexAction(): array
    {
        $homePage = $this->container->get(HomeLandingPageProvider::class)->getHomeLandingPage();

        return ['data' => ['page' => $homePage]];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            HomeLandingPageProvider::class,
        ]);
    }
}
