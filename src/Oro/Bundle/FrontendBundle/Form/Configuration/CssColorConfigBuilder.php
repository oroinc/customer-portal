<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

use Oro\Bundle\FormBundle\Form\Type\OroSimpleColorPickerType;
use Symfony\Component\Validator\Constraints\CssColor;

/**
 * Used to specify type and options for the css_color option
 */
class CssColorConfigBuilder extends AbstractCssConfigBuilder
{
    protected string $parentFormType = OroSimpleColorPickerType::class;

    #[\Override]
    public static function getType(): string
    {
        return 'css_color';
    }

    #[\Override]
    protected function getConfiguredOptions(array $option): array
    {
        $configuredOptions = parent::getConfiguredOptions($option);

        if ($configuredOptions['parentConfig']['class'] !== OroSimpleColorPickerType::class) {
            return $configuredOptions;
        }

        $configuredParentConfigOptions = &$configuredOptions['parentConfig']['options'];

        if (!isset($configuredParentConfigOptions['allow_custom_color'])) {
            $configuredParentConfigOptions['allow_custom_color'] = true;
        }

        if (!isset($configuredParentConfigOptions['show_input_control'])) {
            $configuredParentConfigOptions['show_input_control'] = true;
        }

        return $configuredOptions;
    }

    #[\Override]
    protected function getConstraints(): array
    {
        return [
            new CssColor()
        ];
    }
}
