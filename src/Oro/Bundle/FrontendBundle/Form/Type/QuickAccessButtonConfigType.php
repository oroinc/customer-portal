<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\NavigationBundle\Form\Type\MenuChoiceType;
use Oro\Bundle\WebCatalogBundle\Form\Type\ContentNodeSelectSystemConfigType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents Quick Access Button settings for System Configuration
 */
class QuickAccessButtonConfigType extends AbstractType
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isHasConfiguredWebCatalog = (bool) $this->configManager->get('oro_web_catalog.web_catalog');

        $builder->add('type', ChoiceType::class, [
            'label' => 'oro_frontend.system_configuration.fields.quick_access_button.fields.type.label',
            'tooltip' => 'oro_frontend.system_configuration.fields.quick_access_button.fields.type.tooltip',
            'attr' => [
                'data-dependee-id' => 'quick_access_button_type',
            ],
            'placeholder' => 'oro_frontend.system_configuration.fields.quick_access_button.fields.type.placeholder',
            'choices' => $this->getChoicesForType($isHasConfiguredWebCatalog),
        ]);
        $builder->add('menu', MenuChoiceType::class, [
            'scope_type' => 'menu_frontend_visibility',
            'label' => 'oro_frontend.system_configuration.fields.quick_access_button.fields.menu.label',
            'tooltip' => 'oro_frontend.system_configuration.fields.quick_access_button.fields.menu.tooltip',
            'attr' => [
                'data-page-component-module' => 'orosale/js/app/components/dependent-field-component',
                'data-depend-on' => 'quick_access_button_type',
                'data-show-if' => QuickAccessButtonConfig::TYPE_MENU,
            ],
        ]);
        if ($isHasConfiguredWebCatalog) {
            $builder->add('webCatalogNode', ContentNodeSelectSystemConfigType::class, [
                'label' => 'oro_frontend.system_configuration.fields.quick_access_button.fields.web_catalog_node.label',
                'tooltip' =>
                    'oro_frontend.system_configuration.fields.quick_access_button.fields.web_catalog_node.tooltip',
                'attr' => [
                    'data-page-component-module' => 'orosale/js/app/components/dependent-field-component',
                    'data-depend-on' => 'quick_access_button_type',
                    'data-show-if' => QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE,
                ],
                'data_transformer' => 'oro_web_catalog.form.data_transformer.navigation_root_option',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuickAccessButtonConfig::class,
        ]);
    }

    private function getChoicesForType(bool $isHasConfiguredWebCatalog): array
    {
        $choices = [
            QuickAccessButtonConfig::TYPE_MENU => QuickAccessButtonConfig::TYPE_MENU,
        ];
        if ($isHasConfiguredWebCatalog) {
            $choices[QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE] = QuickAccessButtonConfig::TYPE_WEB_CATALOG_NODE;
        }

        return $choices;
    }
}
