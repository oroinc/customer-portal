<?php

namespace Oro\Bundle\FrontendBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Config\ExcludeCustomFieldsConfigExtra;
use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Sets an initial list of requests for configuration data for Frontend API resources.
 */
class InitializeConfigExtras implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if (!$context->hasConfigExtra(ExcludeCustomFieldsConfigExtra::NAME)) {
            $context->addConfigExtra(new ExcludeCustomFieldsConfigExtra());
        }
    }
}
