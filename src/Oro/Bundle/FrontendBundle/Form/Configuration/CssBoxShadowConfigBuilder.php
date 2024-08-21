<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

/**
 * Used to specify type and options for the css_box_shadow option
 */
class CssBoxShadowConfigBuilder extends AbstractCssConfigBuilder
{
    public static function getType(): string
    {
        return 'css_box_shadow';
    }
}
