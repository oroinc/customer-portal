<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched to collect the parameters of the request a forgot password form was submitted.
 */
class PasswordResetRequestContextCollectEvent extends Event
{
    private array $requestParameters = [];

    public function __construct(
        private readonly Request $request
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }

    public function getRequestParameter(string $name): mixed
    {
        return $this->requestParameters[$name] ?? null;
    }

    public function setRequestParameter(string $name, mixed $value): self
    {
        $this->requestParameters[$name] = $value;

        return $this;
    }
}
