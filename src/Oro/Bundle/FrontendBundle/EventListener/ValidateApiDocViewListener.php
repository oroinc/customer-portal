<?php

namespace Oro\Bundle\FrontendBundle\EventListener;

use Oro\Bundle\ApiBundle\EventListener\ValidateApiDocViewListener as BaseValidateApiDocViewListener;
use Oro\Bundle\FrontendBundle\Api\ApiDoc\RestDocUrlGenerator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks whether the requested API view is valid for frontend/backend REST API sandbox.
 */
class ValidateApiDocViewListener extends BaseValidateApiDocViewListener
{
    /** @var string[] */
    private $frontendViews;

    /** @var string */
    private $frontendDefaultView;

    /**
     * @param string      $basePath
     * @param string[]    $views
     * @param string|null $defaultView
     * @param string[]    $frontendViews
     * @param string|null $frontendDefaultView
     */
    public function __construct(
        string $basePath,
        array $views,
        ?string $defaultView,
        array $frontendViews,
        ?string $frontendDefaultView
    ) {
        parent::__construct($basePath, $views, $defaultView);
        $this->frontendViews = $frontendViews;
        $this->frontendDefaultView = $frontendDefaultView;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isValidView(Request $request): bool
    {
        if (!parent::isValidView($request)) {
            return false;
        }

        $view = $this->getView($request);
        if ($view) {
            if ($this->isFrontendRequest($request)) {
                return \in_array($view, $this->frontendViews, true);
            }

            return !\in_array($view, $this->frontendViews, true);
        }

        return true;
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    protected function getDefaultView(Request $request): ?string
    {
        if ($this->isFrontendRequest($request)) {
            return $this->frontendDefaultView;
        }

        return parent::getDefaultView($request);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isFrontendRequest(Request $request): bool
    {
        return RestDocUrlGenerator::ROUTE === $request->attributes->get('_route');
    }
}
