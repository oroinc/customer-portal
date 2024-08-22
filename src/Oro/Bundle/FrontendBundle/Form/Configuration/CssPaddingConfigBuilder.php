<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

/**
 * Used to specify type and options for the css_padding option
 */
class CssPaddingConfigBuilder extends AbstractCssConfigBuilder
{
    public static function getType(): string
    {
        return 'css_padding';
    }
}
