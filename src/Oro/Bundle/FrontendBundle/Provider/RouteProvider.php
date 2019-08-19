<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\ActionBundle\Provider\RouteProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * The provider for action routes that returns storefront routes for storefront requests
 * and default routes for management console requests.
 */
class RouteProvider implements RouteProviderInterface
{
    /** @var RouteProviderInterface */
    private $routeProvider;

    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var string */
    private $formDialogRoute;

    /** @var string */
    private $formPageRoute;

    /** @var string */
    private $executionRoute;

    /** @var string|null */
    private $widgetRoute;

    /**
     * @param RouteProviderInterface $routeProvider
     * @param FrontendHelper         $frontendHelper
     * @param string                 $formDialogRoute
     * @param string                 $formPageRoute
     * @param string                 $executionRoute
     * @param string|null            $widgetRoute
     */
    public function __construct(
        RouteProviderInterface $routeProvider,
        FrontendHelper $frontendHelper,
        $formDialogRoute,
        $formPageRoute,
        $executionRoute,
        $widgetRoute = null
    ) {
        $this->routeProvider = $routeProvider;
        $this->frontendHelper = $frontendHelper;
        $this->formDialogRoute = $formDialogRoute;
        $this->formPageRoute = $formPageRoute;
        $this->executionRoute = $executionRoute;
        $this->widgetRoute = $widgetRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetRoute()
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->widgetRoute
            : $this->routeProvider->getWidgetRoute();
    }

    /**
     * {@inheritdoc}
     */
    public function getFormDialogRoute()
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->formDialogRoute
            : $this->routeProvider->getFormDialogRoute();
    }

    /**
     * {@inheritdoc}
     */
    public function getFormPageRoute()
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->formPageRoute
            : $this->routeProvider->getFormPageRoute();
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutionRoute()
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->executionRoute
            : $this->routeProvider->getExecutionRoute();
    }
}
