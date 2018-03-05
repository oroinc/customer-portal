<?php

namespace Oro\Bundle\CustomerBundle\Api\ApiDoc;

use Oro\Bundle\ApiBundle\ApiDoc\RestDocUrlGeneratorInterface;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\SignInTargetPathProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides URL to the current API view.
 */
class SignInTargetPathProvider implements SignInTargetPathProviderInterface
{
    /** @var SignInTargetPathProviderInterface */
    private $innerProvider;

    /** @var RequestStack */
    private $requestStack;

    /** @var RestDocUrlGeneratorInterface */
    private $restDocUrlGenerator;

    /**
     * @param SignInTargetPathProviderInterface $innerProvider
     * @param RequestStack                      $requestStack
     * @param RestDocUrlGeneratorInterface      $restDocUrlGenerator
     */
    public function __construct(
        SignInTargetPathProviderInterface $innerProvider,
        RequestStack $requestStack,
        RestDocUrlGeneratorInterface $restDocUrlGenerator
    ) {
        $this->innerProvider = $innerProvider;
        $this->requestStack = $requestStack;
        $this->restDocUrlGenerator = $restDocUrlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetPath(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $view = $request->query->get('_api_view');
            if ($view) {
                return $this->restDocUrlGenerator->generate($view);
            }
        }

        return $this->innerProvider->getTargetPath();
    }
}
