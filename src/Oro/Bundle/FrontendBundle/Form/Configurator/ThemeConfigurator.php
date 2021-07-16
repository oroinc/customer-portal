<?php

namespace Oro\Bundle\FrontendBundle\Form\Configurator;

use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Config\Tree\FieldNodeDefinition;
use Oro\Bundle\ConfigBundle\Exception\ItemNotFoundException;
use Oro\Bundle\FrontendBundle\Form\Type\PageTemplateFormFieldType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ThemeConfigurator
{
    /** @var ConfigBag */
    private $configBag;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(ConfigBag $configBag, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->configBag = $configBag;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function configure(FormBuilderInterface $builder, array $options)
    {
        $fieldDefinition = $this->buildFieldNode('oro_frontend.page_templates');

        if ($fieldDefinition->getAclResource() &&
            !$this->authorizationChecker->isGranted($fieldDefinition->getAclResource())) {
            // field is not allowed to be shown, do nothing
            return;
        }

        $name = str_replace(
            ConfigManager::SECTION_MODEL_SEPARATOR,
            ConfigManager::SECTION_VIEW_SEPARATOR,
            $fieldDefinition->getPropertyPath()
        );

        // take config field options form field definition
        $configFieldOptions = array_intersect_key(
            $fieldDefinition->getOptions(),
            array_flip(['label', 'required', 'block', 'subblock', 'tooltip', 'resettable'])
        );

        // pass only options needed to "value" form type
        $configFieldOptions['target_field_type'] = $fieldDefinition->getType();
        $configFieldOptions['target_field_options'] = array_diff_key(
            $fieldDefinition->getOptions(),
            $configFieldOptions
        );

        if ($fieldDefinition->needsPageReload()) {
            $configFieldOptions['target_field_options']['attr']['data-needs-page-reload'] = '';
            $configFieldOptions['use_parent_field_options']['attr']['data-needs-page-reload'] = '';
        }

        $builder->add($name, PageTemplateFormFieldType::class, $configFieldOptions);
    }

    /**
     * @param string $node
     *
     * @return FieldNodeDefinition
     * @throws ItemNotFoundException
     */
    private function buildFieldNode($node)
    {
        $fieldsRoot = $this->configBag->getFieldsRoot($node);
        if ($fieldsRoot === false) {
            throw new ItemNotFoundException(sprintf('Field "%s" is not defined.', $node));
        }

        return new FieldNodeDefinition($node, $fieldsRoot);
    }
}
