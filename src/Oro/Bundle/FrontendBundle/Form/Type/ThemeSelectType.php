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
    public const NAME = 'oro_frontend_theme_select';
    public const GROUP = 'commerce';

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

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
        ]);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
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

    #[\Override]
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
