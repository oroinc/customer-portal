<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Async\PasswordResetRequestContext;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Provider\AbstractPreferredLocalizationProvider;

/**
 * Returns the localization of the request a forgot password form was submitted in, so that the reset password email
 * sent by a message queue consumer is localized the same way as it is done within the request.
 */
class PasswordResetRequestPreferredLocalizationProvider extends AbstractPreferredLocalizationProvider
{
    public function __construct(
        private readonly PasswordResetRequestContext $passwordResetRequestContext
    ) {
    }

    #[\Override]
    public function supports($entity): bool
    {
        return $entity instanceof CustomerUser && null !== $this->passwordResetRequestContext->getLocalization();
    }

    /**
     * @param CustomerUser $entity
     */
    #[\Override]
    protected function getPreferredLocalizationForEntity($entity): ?Localization
    {
        return $this->passwordResetRequestContext->getLocalization();
    }
}
