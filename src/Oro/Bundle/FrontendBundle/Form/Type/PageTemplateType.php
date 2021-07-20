<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Component\Layout\Extension\Theme\Manager\PageTemplatesManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The base class for form types to select allowed page template types.
 */
class PageTemplateType extends AbstractType
{
    /** @var PageTemplatesManager */
    private $pageTemplatesManager;

    /** @var array */
    private $pageTemplates;

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
                'choices' => function (Options $options) {
                    return array_flip($this->getPageTemplatesData($options['route_name'], 'choices'));
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
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['page-template-metadata'] = $this->getPageTemplatesData($options['route_name'], 'descriptions');
    }

    /**
     * @param string $routeName
     * @param string $key
     * @return array
     */
    private function getPageTemplatesData($routeName, $key): array
    {
        if ($this->pageTemplates === null) {
            $this->pageTemplates = $this->pageTemplatesManager->getRoutePageTemplates();
        }

        return $this->pageTemplates[$routeName][$key] ?? [];
    }
}
