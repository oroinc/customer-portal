<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;

class MenuUserAgentConditionData extends AbstractFixture implements DependentFixtureInterface
{
    const USER_AGENT_CONTAINS = 'user_agent_contains';
    const USER_AGENT_MATCHES = 'user_agent_matches';
    const USER_AGENT_DOES_NOT_CONTAIN = 'user_agent_does_not_contain';
    const USER_AGENT_DOES_NOT_MATCHES = 'user_agent_does_not_matches';

    /** @var array */
    protected static $userAgentConditions = [
        self::USER_AGENT_CONTAINS => [
            'groupIdentifier' => 0,
            'operation' => MenuUserAgentCondition::OPERATION_CONTAINS,
            'value' => 'Mozilla/5.0'
        ],
        self::USER_AGENT_MATCHES => [
            'groupIdentifier' => 0,
            'operation' => MenuUserAgentCondition::OPERATION_MATCHES,
            'value' => 'Safari/537|Chrome/57'
        ],
        self::USER_AGENT_DOES_NOT_CONTAIN => [
            'groupIdentifier' => 1,
            'operation' => MenuUserAgentCondition::OPERATION_DOES_NOT_CONTAIN,
            'value' => 'Mozilla/3.0'
        ],
        self::USER_AGENT_DOES_NOT_MATCHES => [
            'groupIdentifier' => 2,
            'operation' => MenuUserAgentCondition::OPERATION_DOES_NOT_MATCHES,
            'value' => 'TestAgent|Safari/530'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            GlobalMenuUpdateData::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var MenuUpdate $menuUpdate */
        $menuUpdate = $this->getReference(GlobalMenuUpdateData::MENU_UPDATE_2_1);

        foreach (self::$userAgentConditions as $userAgentConditionsReference => $data) {
            $userAgentCondition = new MenuUserAgentCondition();
            $userAgentCondition->setConditionGroupIdentifier($data['groupIdentifier']);
            $userAgentCondition->setOperation($data['operation']);
            $userAgentCondition->setValue($data['value']);
            $userAgentCondition->setMenuUpdate($menuUpdate);
            $manager->persist($userAgentCondition);

            $this->setReference($userAgentConditionsReference, $userAgentCondition);
        }

        $manager->flush();
    }
}
