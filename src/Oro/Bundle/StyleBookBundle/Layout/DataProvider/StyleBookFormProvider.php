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
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
            ->add('radios_disabled', ChoiceType::class, [
                'choices' => [
                    'Disabled Radio' => 0,
                    'Disabled Checked Radio' => 1
                ],
                'choice_attr' => [
                    'Disabled Radio' => [
                        'disabled' => true
                    ],
                    'Disabled Checked Radio' => [
                        'checked' => true,
                        'disabled' => true
                    ]
                ],
                'expanded' => true,
            ])
            ->add('checkboxes', ChoiceType::class, [
                'choices' => [
                    'Cup of coffee' => 0,
                    'Doughnut' => 1
                ],
                'expanded' => true,
                'multiple' => true
            ])
            ->add('checkboxes_disabled', ChoiceType::class, [
                'choices' => [
                    'Disabled Checkbox' => 0,
                    'Disabled Checked Checkbox' => 1
                ],
                'choice_attr' => [
                    'Disabled Checkbox' => [
                        'disabled' => true
                    ],
                    'Disabled Checked Checkbox' => [
                        'checked' => true,
                        'disabled' => true
                    ]
                ],
                'expanded' => true,
                'multiple' => true
            ])
            ->add('switch', ChoiceType::class, [
                'choices' => [
                    'Switch Off' => 0,
                    'Switch On' => 1,
                    'Switch Off Disabled' => 2,
                    'Switch On Disabled' => 3
                ],
                'choice_attr' => [
                    'Switch Off' => [
                        'checked' => false,
                        'role' => 'switch'
                    ],
                    'Switch On' => [
                        'checked' => true,
                        'role' => 'switch'
                    ],
                    'Switch Off Disabled' => [
                        'checked' => false,
                        'disabled' => true,
                        'role' => 'switch'
                    ],
                    'Switch On Disabled' => [
                        'checked' => true,
                        'disabled' => true,
                        'role' => 'switch'
                    ]
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
