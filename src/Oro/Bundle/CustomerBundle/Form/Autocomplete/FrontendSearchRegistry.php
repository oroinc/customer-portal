<?php

namespace Oro\Bundle\CustomerBundle\Form\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistryInterface;
use Oro\Bundle\FormBundle\Exception\NotFoundSearchHandlerException;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Psr\Container\ContainerInterface;

/**
 * The registry of frontend autocomplete search handlers.
 */
class FrontendSearchRegistry implements SearchRegistryInterface
{
    public function __construct(
        private SearchRegistryInterface $innerRegistry,
        private FrontendHelper $frontendRequestHelper
    ) {
    }

    private ContainerInterface $frontendSearchHandlers;

    public function setFrontendSearchHandlers(ContainerInterface $frontendSearchHandlers): void
    {
        $this->frontendSearchHandlers = $frontendSearchHandlers;
    }

    #[\Override]
    public function getSearchHandler(string $name): SearchHandlerInterface
    {
        if ($this->frontendRequestHelper->isFrontendRequest()) {
            if (!$this->frontendSearchHandlers->has($name)) {
                throw new NotFoundSearchHandlerException(sprintf('Search handler "%s" is not registered.', $name));
            }

            return $this->frontendSearchHandlers->get($name);
        }

        return $this->innerRegistry->getSearchHandler($name);
    }

    #[\Override]
    public function hasSearchHandler(string $name): bool
    {
        if ($this->frontendRequestHelper->isFrontendRequest()) {
            return $this->frontendSearchHandlers->has($name);
        }

        return $this->innerRegistry->hasSearchHandler($name);
    }
}
