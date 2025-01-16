<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Request;

use Oro\Bundle\SecurityBundle\Request\SessionStorageOptionsManipulator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * The decorator for HTTP kernel that provides a possibility to use separate session for storefront.
 * Sets cookie path for storefront session cookie if application was installed in subfolder.
 */
class StorefrontSessionHttpKernelDecorator implements HttpKernelInterface, TerminableInterface
{
    private ?array $originalSessionOptions = null;

    private ?array $currentSessionOptions = null;

    public function __construct(
        private HttpKernelInterface $innerKernel,
        private SessionStorageOptionsManipulator $sessionStorageOptionsManipulator,
        private FrontendHelper $frontendHelper,
        private array $storefrontSessionOptions = []
    ) {
    }

    #[\Override]
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        if ($this->frontendHelper->isFrontendUrl($request->getPathInfo())) {
            if (null === $this->originalSessionOptions) {
                $this->originalSessionOptions = $this->sessionStorageOptionsManipulator->getOriginalSessionOptions();
            }

            if ($this->currentSessionOptions === null) {
                $this->currentSessionOptions = $this->storefrontSessionOptions;
                $basePath = $request->getBasePath();
                if ($basePath && '/' !== $basePath) {
                    $existingCookiePath = $this->storefrontSessionOptions['cookie_path'] ?? '/';
                    $this->currentSessionOptions['cookie_path'] = $basePath . $existingCookiePath;
                }
            }

            $this->sessionStorageOptionsManipulator->setSessionOptions(
                array_replace($this->originalSessionOptions, $this->currentSessionOptions)
            );
        }

        return $this->innerKernel->handle($request, $type, $catch);
    }

    #[\Override]
    public function terminate(Request $request, Response $response): void
    {
        if ($this->innerKernel instanceof TerminableInterface) {
            $this->innerKernel->terminate($request, $response);
        }
    }
}
