<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

/**
 * The interface for classes that can provide URL an user should be redirected after login.
 */
interface SignInTargetPathProviderInterface
{
    /**
     * Returns URL an user should be redirected after login.
     */
    public function getTargetPath(): ?string;
}
