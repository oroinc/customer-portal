<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuScreensConditionType extends AbstractType
{
    const NAME = 'oro_commerce_menu_screens_condition';

    /**
     * @var ScreensProviderInterface
     */
    private $screensProvider;

    public function __construct(ScreensProviderInterface $screensProvider)
    {
        $this->screensProvider = $screensProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getScreensChoices(),
            'multiple' => true,
            'required' => false,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return static::NAME;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * @return array
     */
    private function getScreensChoices()
    {
        $screens = $this->screensProvider->getScreens();
        $choices = array_map(function (array $screen) {
            return $screen['label'];
        }, $screens);

        return array_flip($choices);
    }
}
