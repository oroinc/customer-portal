<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\ConditionEvaluatorInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

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

    /**
     * {@inheritDoc}
     */
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
