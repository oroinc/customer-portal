<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\ConditionEvaluatorInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

/**
 * Builds menu items with condition evaluation for display visibility.
 *
 * This menu builder applies condition evaluation to menu items recursively, determining
 * whether each item should be displayed based on configured conditions. It respects existing
 * display settings and only evaluates conditions for items that are not explicitly hidden.
 */
class MenuDisplayBuilder implements BuilderInterface
{
    /**
     * @var ConditionEvaluatorInterface
     */
    private $conditionEvaluator;

    public function __construct(ConditionEvaluatorInterface $conditionEvaluator)
    {
        $this->conditionEvaluator = $conditionEvaluator;
    }

    #[\Override]
    public function build(ItemInterface $menu, array $options = [], $alias = null)
    {
        $this->applyEvaluatorRecursively($menu, $options);
    }

    private function applyEvaluatorRecursively(ItemInterface $menuItem, array $options)
    {
        $menuChildren = $menuItem->getChildren();

        foreach ($menuChildren as $menuChild) {
            $this->applyEvaluatorRecursively($menuChild, $options);
        }

        if ($menuItem->isDisplayed() !== false) {
            $isDisplayed = $this->conditionEvaluator->evaluate($menuItem, $options);
            $menuItem->setDisplay($isDisplayed);
        }
    }
}
