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
    public const RESOURCE_ROUTE = 'oro_frontend_rest_api_doc_resource';

    private RestDocUrlGeneratorInterface $innerGenerator;
    private UrlGeneratorInterface $urlGenerator;
    /** @var string[] */
    private array $frontendViews;
    private ?string $defaultFrontendView;

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

    private function isDefaultView(string $view): bool
    {
        return $this->defaultFrontendView && $view === $this->defaultFrontendView;
    }
}
