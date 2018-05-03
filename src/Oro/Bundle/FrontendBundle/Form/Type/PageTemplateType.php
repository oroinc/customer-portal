<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Component\Layout\Extension\Theme\Manager\PageTemplatesManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageTemplateType extends AbstractType
{
    /** @var PageTemplatesManager */
    private $pageTemplatesManager;

    /**
     * @param PageTemplatesManager $pageTemplatesManager
     */
    public function __construct(PageTemplatesManager $pageTemplatesManager)
    {
        $this->pageTemplatesManager = $pageTemplatesManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['route_name'])
            ->setDefaults([
                // TODO: remove 'choices_as_values' option below in scope of BAP-15236
                'choices_as_values' => true,
                'choices' => function (Options $options) {
                    return $this->getPageTemplatesByRouteName($options['route_name']);
                },
                'placeholder' => 'oro_frontend.system_configuration.fields.no_page_template.label',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @param string $routeName
     * @return array
     */
    private function getPageTemplatesByRouteName($routeName)
    {
        $routePageTemplates = $this->pageTemplatesManager->getRoutePageTemplates();
        if (array_key_exists($routeName, $routePageTemplates)) {
            return $routePageTemplates[$routeName]['choices'];
        }

        return [];
    }
}
