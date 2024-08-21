<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

/**
 * Used to specify type and options for the css_outline option
 */
class CssOutlineConfigBuilder extends AbstractCssConfigBuilder
{
    public static function getType(): string
    {
        return 'css_outline';
    }
}
