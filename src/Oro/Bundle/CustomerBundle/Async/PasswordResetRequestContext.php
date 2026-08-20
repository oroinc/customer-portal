<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Async;

use Oro\Bundle\LocaleBundle\Entity\Localization;

/**
 * Holds the context of the request a forgot password form was submitted in.
 */
class PasswordResetRequestContext
{
    private ?Localization $localization = null;

    private array $requestParameters = [];

    public function getLocalization(): ?Localization
    {
        return $this->localization;
    }

    public function setLocalization(?Localization $localization): void
    {
        $this->localization = $localization;
    }

    public function getRequestParameter(string $name): mixed
    {
        return $this->requestParameters[$name] ?? null;
    }

    public function setRequestParameters(array $requestParameters): void
    {
        $this->requestParameters = $requestParameters;
    }
}
