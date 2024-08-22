<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

/**
 * Used to specify type and options for the css_border_radius option
 */
class CssBorderRadiusConfigBuilder extends AbstractCssConfigBuilder
{
    public static function getType(): string
    {
        return 'css_border_radius';
    }
}
