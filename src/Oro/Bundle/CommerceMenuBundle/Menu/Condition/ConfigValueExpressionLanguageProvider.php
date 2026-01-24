<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\Condition;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Provides the config_value() function for menu condition expression language.
 *
 * This expression language provider registers a function that allows menu conditions to access
 * system configuration values, enabling dynamic menu visibility based on configuration settings.
 */
class ConfigValueExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /** @var ConfigManager */
    private $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('config_value', function ($parameter) {
                return sprintf('config_value(%s)', $parameter);
            }, [$this, 'getConfigValue']),
        ];
    }

    /**
     * @param array  $variables
     * @param string $parameter
     *
     * @return string|null
     */
    public function getConfigValue(array $variables, $parameter)
    {
        return $this->configManager->get($parameter);
    }
}
