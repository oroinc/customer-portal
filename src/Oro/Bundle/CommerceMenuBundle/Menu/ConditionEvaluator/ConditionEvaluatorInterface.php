<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator;

use Knp\Menu\ItemInterface;

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
