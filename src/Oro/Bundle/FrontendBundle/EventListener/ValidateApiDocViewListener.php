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

    /**
     * @param string[] $views
     * @param string[] $frontendViews
     */
    public function __construct(array $views, array $frontendViews)
    {
        parent::__construct($views);
        $this->frontendViews = $frontendViews;
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
     * @return bool
     */
    protected function isFrontendRequest(Request $request): bool
    {
        return RestDocUrlGenerator::ROUTE === $request->attributes->get('_route');
    }
}
