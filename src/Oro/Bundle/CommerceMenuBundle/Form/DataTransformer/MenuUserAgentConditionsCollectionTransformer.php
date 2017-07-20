<?php

namespace Oro\Bundle\CommerceMenuBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Symfony\Component\Form\DataTransformerInterface;

class MenuUserAgentConditionsCollectionTransformer implements DataTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform($menuUserAgentConditionsCollection)
    {
        // If value is already transformed in PRE_SET_DATA, return it as is.
        if (is_array($menuUserAgentConditionsCollection)) {
            return $menuUserAgentConditionsCollection;
        }

        if (!$menuUserAgentConditionsCollection instanceof Collection) {
            return [];
        }

        $groupedConditionsArray = [];
        foreach ($menuUserAgentConditionsCollection as $menuUserAgentCondition) {
            if (!$menuUserAgentCondition instanceof MenuUserAgentCondition) {
                throw new \LogicException('Conditions collection was expected to contain only MenuUserAgentCondition');
            }

            $groupedConditionsArray[$menuUserAgentCondition->getConditionGroupIdentifier()][] =
                $menuUserAgentCondition;
        }

        ksort($groupedConditionsArray);

        return $groupedConditionsArray;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($groupedConditionsArray)
    {
        if (!is_array($groupedConditionsArray)) {
            return new ArrayCollection();
        }

        $menuUserAgentConditionsCollection = [];
        $groupedConditionsArray = array_values($groupedConditionsArray);
        foreach ($groupedConditionsArray as $key => $conditionsGroup) {
            if (!is_array($conditionsGroup)) {
                continue;
            }

            foreach ($conditionsGroup as $menuUserAgentCondition) {
                if (!$menuUserAgentCondition instanceof MenuUserAgentCondition) {
                    throw new \LogicException('Conditions group was expected to contain only MenuUserAgentCondition');
                }

                $menuUserAgentCondition->setConditionGroupIdentifier($key);
                $menuUserAgentConditionsCollection[] = $menuUserAgentCondition;
            }
        }

        return new ArrayCollection($menuUserAgentConditionsCollection);
    }
}
