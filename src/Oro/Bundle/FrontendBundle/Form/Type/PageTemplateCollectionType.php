<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Component\Layout\Extension\Theme\Manager\PageTemplatesManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageTemplateCollectionType extends AbstractType
{
    public const NAME = 'oro_frontend_page_template_collection';

    /**
     * @var PageTemplatesManager
     */
    protected $pageTemplatesManager;

    public function __construct(PageTemplatesManager $pageTemplatesManager)
    {
        $this->pageTemplatesManager = $pageTemplatesManager;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->pageTemplatesManager->getRoutePageTemplates() as $routeName => $routeOptions) {
            $builder->add($routeName, PageTemplateType::class, [
                'route_name' => $routeName,
                'label' => $routeOptions['label']
            ]);
        }
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
