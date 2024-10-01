<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

/**
 * Used to specify type and options for the css_background option
 */
class CssBackgroundConfigBuilder extends AbstractCssConfigBuilder
{
    #[\Override]
    public static function getType(): string
    {
        return 'css_background';
    }
}
