<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

/**
 * Used to specify type and options for the css_line_height option
 */
class CssLineHeightConfigBuilder extends AbstractCssConfigBuilder
{
    #[\Override]
    public static function getType(): string
    {
        return 'css_line_height';
    }
}
