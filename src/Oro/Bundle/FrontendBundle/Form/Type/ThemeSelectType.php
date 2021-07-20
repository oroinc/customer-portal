<?php

namespace Oro\Bundle\FrontendBundle\Form\Type;

use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Theme select type
 */
class ThemeSelectType extends AbstractType
{
    const NAME = 'oro_frontend_theme_select';
    const GROUP = 'commerce';

    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * @var Theme[]
     */
    protected $themes = [];

    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
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
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $metadata = [];
        foreach ($this->getThemes() as $theme) {
            $metadata[$theme->getName()] = [
                'icon' => $theme->getIcon(),
                'logo' => $theme->getLogo(),
                'screenshot' => $theme->getScreenshot(),
                'description' => $theme->getDescription()
            ];
        }
        $view->vars['themes-metadata'] = $metadata;
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $choices = [];

        foreach ($this->getThemes() as $theme) {
            $choices[$theme->getLabel()] = $theme->getName();
        }

        return $choices;
    }

    /**
     * @return Theme[]
     */
    protected function getThemes()
    {
        if (!$this->themes) {
            $this->themes = $this->themeManager->getEnabledThemes(self::GROUP);
        }

        return $this->themes;
    }
}
