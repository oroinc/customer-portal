<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator;

use Knp\Menu\ItemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Evaluates menu item display conditions using expression language.
 *
 * This evaluator uses Symfony's ExpressionLanguage to evaluate condition expressions configured
 * on menu items. It handles evaluation errors gracefully by logging them and returning the default
 * policy (allow display) when conditions cannot be evaluated.
 */
class MenuConditionEvaluator implements ConditionEvaluatorInterface
{
    const CONDITION_KEY = 'condition';
    const DEFAULT_IS_ALLOWED_POLICY = true;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ExpressionLanguage $expressionLanguage, LoggerInterface $logger)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->logger = $logger;
    }

    #[\Override]
    public function evaluate(ItemInterface $menuItem, array $options)
    {
        $result = static::DEFAULT_IS_ALLOWED_POLICY;
        $condition = $this->getConditionString($menuItem);
        if ($condition !== '') {
            try {
                $result = $this->expressionLanguage->evaluate($condition);
            } catch (\Exception $e) {
                $error = sprintf('Exception caught while evaluating menu condition expression: %s', $e->getMessage());
                $this->logger->error($error);
            }
        }

        return (bool)$result;
    }

    /**
     * @param ItemInterface $menuItem
     *
     * @return string
     */
    private function getConditionString(ItemInterface $menuItem)
    {
        return (string)$menuItem->getExtra(self::CONDITION_KEY);
    }
}
