<?php

namespace Oro\Bundle\FrontendBundle\Layout\Extension;

use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfigurationExtensionInterface;
use Oro\Bundle\ProductBundle\Form\Configuration\ProductPageTemplateBuilder;
use Oro\Bundle\ProductBundle\Provider\PageTemplateProvider;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Gets values from {@link ProductPageTemplateBuilder::getType()} theme configuration types
 * from Resources/views/layouts/{folder}/theme.yml and adds for page_templates.
 */
class PageTemplatesThemeConfigurationExtension implements ThemeConfigurationExtensionInterface
{
    #[\Override]
    public function getConfigFileNames(): array
    {
        return [];
    }

    #[\Override]
    public function appendConfig(NodeBuilder $configNode): void
    {
        $configNode
            ->end()
            ->end()
            ->end()
            ->end()
            ->validate()
                ->always($this->appendPageTemplates(...))
            ->end();
    }

    private function appendPageTemplates(array $configsByTheme): array
    {
        foreach ($configsByTheme as &$config) {
            if (!isset($config['configuration'])) {
                continue;
            }

            foreach ($config['configuration']['sections'] ?? [] as $section) {
                foreach ($section['options'] ?? [] as $option) {
                    if ($option['type'] !== ProductPageTemplateBuilder::getType()) {
                        continue;
                    }

                    foreach ($option['values'] as $key => $label) {
                        $config['config']['page_templates']['templates'][] = [
                            'key' => $key,
                            'label' => $label,
                            'route_name' => PageTemplateProvider::PRODUCT_DETAILS_PAGE_TEMPLATE_ROUTE_NAME,
                        ];
                    }
                }
            }
        }

        return $configsByTheme;
    }
}
