<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\UIBundle\Provider\UserAgentProviderInterface;

/**
 * Evaluates expression in "userAgentConditions" option.
 */
class UserAgentConditionsEvaluator implements ConditionEvaluatorInterface
{
    const MENU_CONDITION_KEY_EXTRA = 'userAgentConditions';

    /**
     * @var UserAgentProviderInterface
     */
    private $userAgentProvider;

    public function __construct(UserAgentProviderInterface $userAgentProvider)
    {
        $this->userAgentProvider = $userAgentProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate(ItemInterface $menuItem, array $options)
    {
        $menuUserAgentConditionsCollection = $menuItem->getExtra(self::MENU_CONDITION_KEY_EXTRA);
        if (!$menuUserAgentConditionsCollection || $menuUserAgentConditionsCollection->isEmpty()) {
            return true;
        }

        $userAgentValue = $this->userAgentProvider->getUserAgent()->getUserAgent();

        $groupedConditionsArray = [];
        foreach ($menuUserAgentConditionsCollection as $menuUserAgentCondition) {
            if (!$menuUserAgentCondition instanceof MenuUserAgentCondition) {
                throw new \LogicException('Conditions collection was expected to contain only MenuUserAgentCondition');
            }

            $booleanOperationResult = $this->checkResultByOperation(
                $menuUserAgentCondition->getOperation(),
                $userAgentValue,
                $menuUserAgentCondition->getValue()
            );

            $conditionGroupIdentifier = $menuUserAgentCondition->getConditionGroupIdentifier();
            $groupLogicResult = ($groupedConditionsArray[$conditionGroupIdentifier] ?? true) && $booleanOperationResult;
            $groupedConditionsArray[$conditionGroupIdentifier] = $groupLogicResult;
        }

        return in_array(true, $groupedConditionsArray);
    }

    /**
     * @param string $operation
     * @param string $userAgent
     * @param string $value
     *
     * @return bool
     * @throws \Exception
     */
    private function checkResultByOperation($operation, $userAgent, $value)
    {
        switch ($operation) {
            case MenuUserAgentCondition::OPERATION_CONTAINS:
                return (str_contains($userAgent, $value));
            case MenuUserAgentCondition::OPERATION_DOES_NOT_CONTAIN:
                return (!str_contains($userAgent, $value));
            case MenuUserAgentCondition::OPERATION_MATCHES:
                return (preg_match('#'.str_replace('#', '\#', $value).'#', $userAgent) !== 0);
            case MenuUserAgentCondition::OPERATION_DOES_NOT_MATCHES:
                return !preg_match('#'.str_replace('#', '\#', $value).'#', $userAgent);
            default:
                throw new \Exception('Unknown operation '. $operation);
        }
    }
}
