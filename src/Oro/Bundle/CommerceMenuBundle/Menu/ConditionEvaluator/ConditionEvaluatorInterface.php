<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator;

use Knp\Menu\ItemInterface;

/**
 * Defines the contract for evaluating menu item display conditions.
 *
 * Implementations of this interface evaluate conditions configured on menu items to determine
 * whether they should be displayed based on the current context and options.
 */
interface ConditionEvaluatorInterface
{
    /**
     * @param ItemInterface $menuItem
     * @param array         $options
     *
     * @return boolean
     */
    public function evaluate(ItemInterface $menuItem, array $options);
}
