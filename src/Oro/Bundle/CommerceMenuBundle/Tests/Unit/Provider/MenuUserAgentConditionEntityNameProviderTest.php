<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Provider;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuUserAgentConditionEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuUserAgentConditionEntityNameProviderTest extends TestCase
{
    private MenuUserAgentConditionEntityNameProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::any())
            ->method('trans')
            ->willReturnCallback(static fn (string $key, array $parameters): string => match ($key) {
                'oro.commercemenu.menu_user_agent_condition.name' => strtr(
                    'Group %group%: %operation% "%value%"',
                    $parameters
                ),
                'oro.commercemenu.menu_user_agent_condition.operation.contains.label' => 'contains',
                'oro.commercemenu.menu_user_agent_condition.operation.does_not_match.label' => 'does not match',
                default => $key,
            });

        $this->provider = new MenuUserAgentConditionEntityNameProvider($translator);
    }

    public function testNamesAConditionTheWayTheMenuItemFormWordsIt(): void
    {
        self::assertSame(
            'Group 1: contains "Mobile"',
            $this->provider->getName(
                EntityNameProviderInterface::FULL,
                null,
                $this->createCondition(0, MenuUserAgentCondition::OPERATION_CONTAINS, 'Mobile')
            )
        );
    }

    public function testSaysWhichGroupOfConditionsAConditionBelongsTo(): void
    {
        self::assertSame(
            'Group 2: does not match "iPhone"',
            $this->provider->getName(
                EntityNameProviderInterface::FULL,
                new Localization(),
                $this->createCondition(1, MenuUserAgentCondition::OPERATION_DOES_NOT_MATCHES, 'iPhone')
            )
        );
    }

    public function testNamesNothingButAUserAgentCondition(): void
    {
        self::assertFalse($this->provider->getName(EntityNameProviderInterface::FULL, null, new \stdClass()));
    }

    public function testNamesNoConditionInAQuery(): void
    {
        self::assertFalse($this->provider->getNameDQL(
            EntityNameProviderInterface::FULL,
            null,
            MenuUserAgentCondition::class,
            'condition'
        ));
    }

    private function createCondition(int $group, string $operation, string $value): MenuUserAgentCondition
    {
        $condition = new MenuUserAgentCondition();
        $condition->setConditionGroupIdentifier($group);
        $condition->setOperation($operation);
        $condition->setValue($value);

        return $condition;
    }
}
