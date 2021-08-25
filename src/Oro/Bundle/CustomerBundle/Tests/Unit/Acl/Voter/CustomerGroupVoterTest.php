<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerGroupVoter;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CustomerGroupVoterTest extends \PHPUnit\Framework\TestCase
{
    private const DEFAULT_GROUP_ID = 1;

    /** @var CustomerGroupVoter */
    private $voter;

    protected function setUp(): void
    {
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->willReturnCallback(function ($group) {
                return $group instanceof CustomerGroup ? $group->getId() : null;
            });

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(self::DEFAULT_GROUP_ID);

        $container = TestContainerBuilder::create()
            ->add('oro_config.global', $configManager)
            ->getContainer($this);

        $this->voter = new CustomerGroupVoter($doctrineHelper, $container);
        $this->voter->setClassName(CustomerGroup::class);
    }

    /**
     * @dataProvider voteDataProvider
     */
    public function testVote(object $object, int $result, string $attribute)
    {
        $this->assertSame(
            $result,
            $this->voter->vote($this->createMock(TokenInterface::class), $object, [$attribute])
        );
    }

    public function voteDataProvider(): array
    {
        return [
            'denied when default group' => [
                'object' => $this->getGroup(self::DEFAULT_GROUP_ID),
                'result' => VoterInterface::ACCESS_DENIED,
                'attribute' => 'DELETE',
            ],
            'abstain when not default group' => [
                'object' => $this->getGroup(2),
                'result' => VoterInterface::ACCESS_ABSTAIN,
                'attribute' => 'DELETE',
            ],
            'abstain when not supported attribute' => [
                'object' => $this->getGroup(2),
                'result' => VoterInterface::ACCESS_ABSTAIN,
                'attribute' => 'VIEW',
            ],
            'abstain when another entity' => [
                'object' => new Customer(),
                'result' => VoterInterface::ACCESS_ABSTAIN,
                'attribute' => 'DELETE',
            ],
        ];
    }

    private function getGroup(int $id): CustomerGroup
    {
        $group = new CustomerGroup();
        ReflectionUtil::setId($group, $id);

        return $group;
    }
}
