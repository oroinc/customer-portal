<?php

namespace Oro\Bundle\FrontendBundle\Form\OptionsConfigurator;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides configuration data for Rule expression editor
 */
class RuleEditorOptionsConfigurator
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['rootEntities']);
        $resolver->setDefined(['dataProviderConfig', 'dataSource', 'allowedOperations', 'pageComponent', 'attr']);

        $resolver->setDefault('pageComponent', 'oroform/js/app/components/expression-editor-component');
        $resolver->setDefault('pageComponentOptions', []);
        $resolver->setDefault('dataSource', []);

        $resolver->setAllowedTypes('rootEntities', 'array');
        $resolver->setAllowedTypes('dataProviderConfig', 'array');
        $resolver->setAllowedTypes('dataSource', 'array');
        $resolver->setAllowedTypes('allowedOperations', 'array');
        $resolver->setAllowedTypes('pageComponent', 'string');

        $resolver->setNormalizer('attr', function (Options $options, $attr) {
            $pageComponentOptions = $options['pageComponentOptions'] + [
                'rootEntities' => $options['rootEntities'],
            ];
            if (isset($options['dataProviderConfig'])) {
                $pageComponentOptions['dataProviderConfig'] = $options['dataProviderConfig'];
            }
            if (isset($options['dataSource'])) {
                $pageComponentOptions['dataSource'] = $options['dataSource'];
            }
            if (isset($options['allowedOperations'])) {
                $pageComponentOptions['allowedOperations'] = $options['allowedOperations'];
            }
            $attr['data-page-component-options'] = json_encode($pageComponentOptions);
            $attr['data-page-component-module'] = $options['pageComponent'];

            return $attr;
        });
    }
}
