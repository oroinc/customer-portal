<?php

namespace Oro\Bundle\FrontendBundle\Form\OptionsConfigurator;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleEditorOptionsConfigurator
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['entities']);
        $resolver->setDefined(['allowedOperations', 'dataSource', 'pageComponent', 'attr']);

        $resolver->setDefault('pageComponent', 'oroui/js/app/components/view-component');
        $resolver->setDefault('pageComponentOptions', [
            'view' => 'oroform/js/app/views/expression-editor-view',
        ]);
        $resolver->setDefault('dataSource', []);

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
            $attr['data-page-component-options'] = json_encode($pageComponentOptions);
            $attr['data-page-component-module'] = $options['pageComponent'];

            return $attr;
        });
    }
}
