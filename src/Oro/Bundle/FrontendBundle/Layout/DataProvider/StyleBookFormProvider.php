<?php

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Symfony\Component\Form\FormView;

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
                'label' => 'Count chickens before they hatch'
            ])
            ->add('radio', 'radio', [
                'label' => 'You have no choice but select this option. This is also irreversible.',
            ])
            ->add('checkboxes', 'choice', [
                'choices' => ['Cup of coffee', 'Doughnut'],
                'expanded' => true,
                'multiple' => true
            ])
            ->add('radios', 'choice', [
                'choices' => ['Dine In', 'To Go'],
                'expanded' => true,
            ])
            ->add('select', 'choice', [
                'choices' => ['Dine In', 'To Go'],
            ])
            ->add('multiselect', 'choice', [
                'choices' => ['Cup of coffee', 'Doughnut'],
                'multiple' => true,
            ])
            ->add('datetime', 'oro_date')
            ->add('textarea', 'textarea');

        return $form->createView();
    }
}
