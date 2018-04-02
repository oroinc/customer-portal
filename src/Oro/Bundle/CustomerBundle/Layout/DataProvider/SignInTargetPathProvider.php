<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

/**
 * The default implementation of the target path provider.
 */
class SignInTargetPathProvider implements SignInTargetPathProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTargetPath(): ?string
    {
        return null;
    }
}
