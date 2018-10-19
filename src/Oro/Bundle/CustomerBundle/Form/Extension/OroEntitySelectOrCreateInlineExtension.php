<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OroEntitySelectOrCreateInlineExtension extends AbstractTypeExtension
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        if ($this->isFrontend()) {
            $resolver->setDefault('grid_widget_route', 'oro_frontend_datagrid_widget');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Search queries must be routed to frontend instead of backend when called from frontend
        if ($this->isFrontend() && isset($view->vars['configs']['route_name'])
            && $view->vars['configs']['route_name'] === 'oro_form_autocomplete_search'
        ) {
            $view->vars['configs']['route_name'] = 'oro_frontend_autocomplete_search';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    /**
     * @return bool
     */
    protected function isFrontend()
    {
        $token = $this->tokenStorage->getToken();

        return $token && $token->getUser() instanceof CustomerUser;
    }
}
