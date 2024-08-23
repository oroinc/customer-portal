<?php

namespace Oro\Bundle\FrontendBundle\Form\Configuration;

use Oro\Bundle\FrontendBundle\Form\Type\CssVariableType;
use Oro\Bundle\FrontendBundle\Model\CssVariableConfig;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Form\Configuration\AbstractConfigurationChildBuilder;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Represents css option config builder for theme.yml
 */
abstract class AbstractCssConfigBuilder extends AbstractConfigurationChildBuilder
{
    protected string $regexPattern = '/^(?!%)[a-zA-Z#0-9\.\s%\/]+$/';

    protected string $parentFormType = TextType::class;

    public function __construct(Packages $packages, protected readonly TranslatorInterface $translator)
    {
        parent::__construct($packages);
    }

    public function buildOption(FormBuilderInterface $builder, array $option): void
    {
        $builder->add(
            $option['name'],
            $this->getTypeClass(),
            array_merge($this->getDefaultOptions(), $this->getConfiguredOptions($option))
        );

        $builder
            ->get($option['name'])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($option) {
                if (!\array_key_exists('default', $option)) {
                    return;
                }

                /**
                 * @var CssVariableConfig $modelData
                 */
                $modelData = $event->getData();

                if (!$modelData) {
                    $modelData = new CssVariableConfig();
                }

                if (!$modelData->getValue()) {
                    $modelData->setValue($option['default']);

                    $event->setData($modelData);
                }
            })
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($option) {
                /**
                 * @var CssVariableConfig $cssVariableConfig
                 */
                $cssVariableConfig = $event->getData();

                $cssVariableConfig->setVariableName($this->resolveCssVariableName($option));
            });
    }

    public function setRegexPattern(string $pattern): void
    {
        $this->regexPattern = $pattern;
    }

    public function setParentFormType(string $parentFormType): void
    {
        $this->parentFormType = $parentFormType;
    }

    protected function getDefaultOptions(): array
    {
        return [
            'required' => false
        ];
    }

    protected function getTypeClass(): string
    {
        return CssVariableType::class;
    }

    protected function getConfiguredOptions(array $option): array
    {
        $configuredOptions = parent::getConfiguredOptions($option);

        $configuredOptions['empty_data'] = new CssVariableConfig();
        $configuredOptions['by_reference'] = false;
        $configuredOptions['constraints'] = [];
        $configuredOptions['parentConfig'] = array_merge(
            [
                'class' => $option['options']['parentConfig']['class'] ?? $this->parentFormType,
                'constraints' => $option['options']['constraints'] ?? $this->getConstraints(),
                'options' => $option['options']['parentConfig']['options'] ?? []
            ]
        );

        return $configuredOptions;
    }

    protected function getConstraints(): array
    {
        return [
            new Regex([
                'pattern' => $this->regexPattern,
                'message' => $this->getInvalidValueMessage()
            ]),
        ];
    }

    protected function getInvalidValueMessage(): string
    {
        return $this->translator->trans(
            'oro_frontend.css_variables.' . $this->getType(),
            [],
            'validators'
        );
    }

    private function resolveCssVariableName(array $option): string
    {
        [, $variableName] = explode(ThemeConfiguration::OPTION_KEY_DELIMITER, $option['name']);

        if (isset($option['options']['cssVariableName'])) {
            return $option['options']['cssVariableName'];
        }

        return str_replace('_', '-', $variableName);
    }
}
