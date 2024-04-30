<?php

namespace Oro\Bundle\FrontendBundle\Api\ApiDoc\OpenApi\Provider;

use Oro\Bundle\ApiBundle\ApiDoc\OpenApi\Provider\OpenApiSpecificationNameProviderInterface;
use Oro\Bundle\ApiBundle\ApiDoc\RestDocViewDetector;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides OpenAPI specification name for storefront APIs.
 */
class OpenApiSpecificationNameProvider implements OpenApiSpecificationNameProviderInterface
{
    private OpenApiSpecificationNameProviderInterface $innerProvider;
    private RestDocViewDetector $docViewDetector;
    private TranslatorInterface $translator;

    public function __construct(
        OpenApiSpecificationNameProviderInterface $innerProvider,
        RestDocViewDetector $docViewDetector,
        TranslatorInterface $translator
    ) {
        $this->innerProvider = $innerProvider;
        $this->docViewDetector = $docViewDetector;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getOpenApiSpecificationName(string $view): string
    {
        $name = $this->innerProvider->getOpenApiSpecificationName($view);

        $previousView = $this->docViewDetector->getView();
        if ($previousView === $view) {
            return $this->resolveOpenApiSpecificationName($name);
        }

        $this->docViewDetector->setView($view);
        try {
            return $this->resolveOpenApiSpecificationName($name);
        } finally {
            $this->docViewDetector->setView($previousView);
        }
    }

    private function resolveOpenApiSpecificationName(string $name): string
    {
        return $this->docViewDetector->getRequestType()->contains('frontend')
            ? $this->translator->trans('oro_frontend.open_api.name_template', ['%name%' => $name])
            : $name;
    }
}
