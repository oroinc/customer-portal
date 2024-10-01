<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

/**
 * Used to specify type and options for the css_min_height option
 */
class CssMinHeightConfigBuilder extends AbstractCssConfigBuilder
{
    #[\Override]
    public static function getType(): string
    {
        return 'css_min_height';
    }
}
