<?php

namespace Oro\Bundle\FrontendBundle\Form\OptionsConfigurator;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorOptionsConfigurator
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['entities']);
        $resolver->setDefined(['dataProviderConfig', 'allowedOperations', 'dataSource', 'pageComponent', 'attr']);

        $resolver->setDefault('pageComponent', 'oroform/js/app/components/expression-editor-component');
        $resolver->setDefault('pageComponentOptions', []);
        $resolver->setDefault('dataSource', []);

        $resolver->setAllowedTypes('dataProviderConfig', 'array');
        $resolver->setAllowedTypes('allowedOperations', 'array');
        $resolver->setAllowedTypes('dataSource', 'array');
        $resolver->setAllowedTypes('entities', 'array');
        $resolver->setAllowedTypes('pageComponent', 'string');

        $resolver->setNormalizer('attr', function (Options $options, $attr) {
            $pageComponentOptions = $options['pageComponentOptions'] + [
                'entities' => $options['entities'],
            ];
            if (isset($options['dataSource'])) {
                $pageComponentOptions['dataSource'] = $options['dataSource'];
            }
            if (isset($options['allowedOperations'])) {
                $pageComponentOptions['allowedOperations'] = $options['allowedOperations'];
            }
            if (isset($options['dataProviderConfig'])) {
                $pageComponentOptions['dataProviderConfig'] = $options['dataProviderConfig'];
            }
            $attr['data-page-component-options'] = json_encode($pageComponentOptions);
            $attr['data-page-component-module'] = $options['pageComponent'];

            return $attr;
        });
    }
}
