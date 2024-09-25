<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Type\QuickAccessButtonConfigType;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\ThemeBundle\Form\Configuration\AbstractConfigurationChildBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Used to specify type and options for the quick_access_button_config
 */
class QuickAccessButtonConfigBuilder extends AbstractConfigurationChildBuilder
{
    public const VIEW_MODULE_NAME = 'orofrontend/theme-configuration-preview-quick-access-button-view';

    #[\Override]
    public static function getType(): string
    {
        return 'quick_access_button_config';
    }

    #[\Override]
    protected function getTypeClass(): string
    {
        return QuickAccessButtonConfigType::class;
    }

    #[\Override]
    protected function getConfiguredOptions(array $option): array
    {
        $configuredOptions = parent::getConfiguredOptions($option);

        $configuredOptions['empty_data'] = new QuickAccessButtonConfig();
        $configuredOptions['by_reference'] = false;

        return $configuredOptions;
    }

    #[\Override]
    protected function getDefaultOptions(): array
    {
        return [];
    }

    #[\Override]
    public function finishView(
        FormView $view,
        FormInterface $form,
        array $formOptions,
        array $themeOption
    ): void {
        parent::finishView($view, $form, $formOptions, $themeOption);

        foreach ($themeOption['previews'] ?? [] as $value => $preview) {
            if ($value === static::DEFAULT_PREVIEW_KEY) {
                continue;
            }

            $view->vars['attr']["data-preview-$value"] = $this->getOptionPreview($themeOption, $value);
        }
    }

    #[\Override]
    protected function getOptionPreview(array $option, mixed $value = null, bool $default = false): ?string
    {
        $value = $value instanceof QuickAccessButtonConfig ? $value->getType() : $value;

        return parent::getOptionPreview($option, $value, $default);
    }
}
