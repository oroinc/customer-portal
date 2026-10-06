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

    public function getLocalization(): ?Localization
    {
        return $this->localization;
    }

    public function setLocalization(?Localization $localization): void
    {
        $this->localization = $localization;
    }
}
