<?php

namespace Oro\Bundle\FrontendBundle\Api\ApiDoc;

use Oro\Bundle\ApiBundle\ApiDoc\RestDocUrlGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The API view URL generator for frontend views.
 */
class RestDocUrlGenerator implements RestDocUrlGeneratorInterface
{
    public const ROUTE = 'oro_frontend_rest_api_doc';

    /** @var RestDocUrlGeneratorInterface */
    private $innerGenerator;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var string[] */
    private $frontendViews;

    /** @var string|null */
    private $defaultFrontendView;

    /**
     * @param RestDocUrlGeneratorInterface $innerGenerator
     * @param UrlGeneratorInterface        $urlGenerator
     * @param string[]                     $frontendViews
     * @param string|null                  $defaultFrontendView
     */
    public function __construct(
        RestDocUrlGeneratorInterface $innerGenerator,
        UrlGeneratorInterface $urlGenerator,
        array $frontendViews,
        ?string $defaultFrontendView
    ) {
        $this->innerGenerator = $innerGenerator;
        $this->urlGenerator = $urlGenerator;
        $this->frontendViews = $frontendViews;
        $this->defaultFrontendView = $defaultFrontendView;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $view): string
    {
        if (!\in_array($view, $this->frontendViews, true)) {
            return $this->innerGenerator->generate($view);
        }

        $parameters = [];
        if (!$this->isDefaultView($view)) {
            $parameters['view'] = $view;
        }

        return $this->urlGenerator->generate(self::ROUTE, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param string $view
     *
     * @return bool
     */
    private function isDefaultView(string $view): bool
    {
        return $this->defaultFrontendView && $view === $this->defaultFrontendView;
    }
}
