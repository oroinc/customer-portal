<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Substitutes "grid_widget_route" option and "route_name" view option
 * if {@see \Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType} form is used on the storefront.
 */
class OroEntitySelectOrCreateInlineExtension extends AbstractTypeExtension
{
    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            $resolver->setDefault('grid_widget_route', 'oro_frontend_datagrid_widget');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Search queries must be routed to frontend instead of backend when called from frontend
        if ($this->frontendHelper->isFrontendRequest()
            && isset($view->vars['configs']['route_name'])
            && $view->vars['configs']['route_name'] === 'oro_form_autocomplete_search'
        ) {
            $view->vars['configs']['route_name'] = 'oro_frontend_autocomplete_search';
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [OroEntitySelectOrCreateInlineType::class];
    }
}
