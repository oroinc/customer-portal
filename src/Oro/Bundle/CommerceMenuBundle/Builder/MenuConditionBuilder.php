<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class MenuConditionBuilder implements BuilderInterface
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

    /**
     * @param ExpressionLanguage $expressionLanguage
     * @param LoggerInterface    $logger
     */
    public function __construct(ExpressionLanguage $expressionLanguage, LoggerInterface $logger)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null)
    {
        $this->applyConditionsRecursively($menu);
    }

    /**
     * @param ItemInterface $menu
     */
    private function applyConditionsRecursively(ItemInterface $menu)
    {
        $menuChildren = $menu->getChildren();
        foreach ($menuChildren as $menuChild) {
            $this->applyConditionsRecursively($menuChild);
        }

        if ($menu->isDisplayed() !== false) {
            $condition = (string)$menu->getExtra(self::CONDITION_KEY);
            $result = $this->evaluateExpression($condition);
            $menu->setDisplay($result);
        }
    }

    /**
     * @param string $condition
     *
     * @return bool
     */
    private function evaluateExpression($condition)
    {
        try {
            $result = $condition ? $this->expressionLanguage->evaluate($condition) : static::DEFAULT_IS_ALLOWED_POLICY;
        } catch (\Exception $e) {
            $result = false;
            $error = sprintf('Exception caught while evaluating menu condition expression: %s', $e->getMessage());
            $this->logger->error($error);
        }

        return (bool)$result;
    }
}
