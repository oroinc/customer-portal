<?php

namespace Oro\Bundle\StyleBookBundle\Layout\DataProvider;

use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;

/**
 * Provides form controls to display on style book pages
 */
class StyleBookFormProvider extends AbstractFormProvider
{
    /**
     * @return FormView
     */
    public function getStyleBookFormView()
    {
        $form = $this->getForm(FormType::class);

        $form->add('text', TextType::class)
            ->add('password', PasswordType::class)
            ->add('checkbox', CheckboxType::class, [
                'label' => 'Count chickens before they hatch'
            ])
            ->add('radio', RadioType::class, [
                'label' => 'You have no choice but select this option. This is also irreversible.',
            ])
            ->add('checkboxes', ChoiceType::class, [
                'choices' => [
                    'Cup of coffee' => 0,
                    'Doughnut' => 1
                ],
                'expanded' => true,
                'multiple' => true
            ])
            ->add('radios', ChoiceType::class, [
                'choices' => [
                    'Dine In' => 0,
                    'To Go' => 1
                ],
                'expanded' => true,
            ])
            ->add('select', ChoiceType::class, [
                'choices' => [
                    'Dine In' => 0,
                    'To Go' => 1
                ],
            ])
            ->add('multiselect', ChoiceType::class, [
                'choices' => [
                    'Cup of coffee' => 0,
                    'Doughnut' => 1
                ],
                'multiple' => true,
            ])
            ->add('datetime', OroDateType::class)
            ->add('textarea', TextareaType::class);

        return $form->createView();
    }
}
