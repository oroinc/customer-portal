<?php

namespace Oro\Bundle\FrontendBundle\Request;

use Oro\Component\PhpUtils\ReflectionUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * The decorator for HTTP kernel that provides a possibility to use separate sessions
 * for storefront and management console.
 */
class DynamicSessionHttpKernelDecorator implements HttpKernelInterface, TerminableInterface
{
    private const SESSION_OPTIONS_PARAMETER_NAME = 'session.storage.options';

    /** @var HttpKernelInterface */
    private $kernel;

    /** @var ContainerInterface */
    private $container;

    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var array */
    private $frontendSessionOptions;

    /** @var array|null */
    private $backendSessionOptions;

    /** @var bool */
    private $isFrontendSessionOptionsApplied = false;

    /**
     * @param HttpKernelInterface $kernel
     * @param ContainerInterface  $container
     * @param FrontendHelper      $frontendHelper
     * @param array               $frontendSessionOptions
     */
    public function __construct(
        HttpKernelInterface $kernel,
        ContainerInterface $container,
        FrontendHelper $frontendHelper,
        array $frontendSessionOptions
    ) {
        $this->kernel = $kernel;
        $this->container = $container;
        $this->frontendHelper = $frontendHelper;
        $this->frontendSessionOptions = $frontendSessionOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if ($this->frontendHelper->isFrontendRequest($request)) {
            $options = $this->getSessionOptions();
            if (null === $this->backendSessionOptions) {
                $this->backendSessionOptions = $options;
            }
            $this->setSessionOptions(array_replace($options, $this->frontendSessionOptions));
            $this->isFrontendSessionOptionsApplied = true;
        } elseif ($this->isFrontendSessionOptionsApplied && null !== $this->backendSessionOptions) {
            $this->setSessionOptions($this->backendSessionOptions);
            $this->isFrontendSessionOptionsApplied = false;
        }

        return $this->kernel->handle($request, $type, $catch);
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Request $request, Response $response)
    {
        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }
    }

    /**
     * @return array
     */
    private function getSessionOptions(): array
    {
        return $this->container->getParameter(self::SESSION_OPTIONS_PARAMETER_NAME);
    }

    /**
     * @param array $options
     */
    private function setSessionOptions(array $options): void
    {
        $parametersProperty = ReflectionUtil::getProperty(new \ReflectionClass($this->container), 'parameters');
        if (null === $parametersProperty) {
            throw new \LogicException(sprintf(
                'The class "%s" does not have "parameters" property.',
                get_class($this->container)
            ));
        }
        $parametersProperty->setAccessible(true);
        $parameters = $parametersProperty->getValue($this->container);
        $parameters[self::SESSION_OPTIONS_PARAMETER_NAME] = $options;
        $parametersProperty->setValue($this->container, $parameters);
    }
}
