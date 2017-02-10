<?php

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Symfony\Component\Form\FormView;

use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;

class StyleBookFormProvider extends AbstractFormProvider
{
    /**
     * @return FormView
     */
    public function getStyleBookFormView()
    {
        $form = $this->getForm('Symfony\Component\Form\Extension\Core\Type\FormType');

        $form->add('text', 'text')
            ->add('password', 'password')
            ->add('checkbox', 'checkbox', [
                'label' => 'Checkbox'
            ])
            ->add('radio', 'radio', [
                'label' => 'Radio',
            ])
            ->add('checkboxes', 'choice', [
                'choices' => ['OroCommerce', 'OroCRM'],
                'expanded' => true,
                'multiple' => true
            ])
            ->add('radios', 'choice', [
                'choices' => ['OroCommerce', 'OroCRM'],
                'expanded' => true,
            ])
            ->add('select', 'choice', [
                'choices' => ['OroCommerce', 'OroCRM'],
            ])
            ->add('multiselect', 'choice', [
                'choices' => ['OroCommerce', 'OroCRM'],
                'multiple' => true,
            ])
            ->add('datetime', 'oro_date')
            ->add('textarea', 'textarea');

        return $form->createView();
    }
}
