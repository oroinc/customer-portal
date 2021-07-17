<?php

namespace Oro\Bundle\FrontendBundle\Request;

use Oro\Bundle\SecurityBundle\Request\SessionHttpKernelDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The decorator for HTTP kernel that provides a possibility to use separate sessions
 * for storefront and management console.
 * Sets cookie path for storefront session cookie if application was installed in subfolder.
 */
class DynamicSessionHttpKernelDecorator extends SessionHttpKernelDecorator
{
    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var array */
    private $frontendSessionOptions;

    /** @var array|null */
    private $backendSessionOptions;

    public function __construct(
        HttpKernelInterface $kernel,
        ContainerInterface $container,
        FrontendHelper $frontendHelper,
        array $frontendSessionOptions
    ) {
        parent::__construct($kernel, $container);
        $this->frontendHelper = $frontendHelper;
        $this->frontendSessionOptions = $frontendSessionOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $basePath = $request->getBasePath();
        if ($this->frontendHelper->isFrontendUrl($request->getPathInfo())) {
            $frontendSessionOptions = $this->applyBasePathToCookiePath($basePath, $this->frontendSessionOptions);
            $options = $this->getSessionOptions();
            if (null === $this->backendSessionOptions) {
                $this->backendSessionOptions = $options;
            }
            $this->setSessionOptions(array_replace($options, $frontendSessionOptions));
        } else {
            if (null !== $this->backendSessionOptions) {
                $options = $this->backendSessionOptions;
            } else {
                $options = $this->getSessionOptions();
                $this->backendSessionOptions = $options;
            }

            $options = $this->applyBasePathToCookiePath($basePath, $options);
            $this->setSessionOptions($options);
        }

        return $this->kernel->handle($request, $type, $catch);
    }
}
