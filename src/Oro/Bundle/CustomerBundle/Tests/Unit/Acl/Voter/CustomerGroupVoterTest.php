<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerGroupVoter;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerGroupRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
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
        $organization = new Organization();
        $customerGroup = new CustomerGroup();
        $customerGroup->setOrganization($organization);

        $repository = $this->createMock(CustomerGroupRepository::class);
        $repository->expects($this->any())
            ->method('find')
            ->willReturn($customerGroup);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->willReturnCallback(function ($group) {
                return $group instanceof CustomerGroup ? $group->getId() : null;
            });
        $doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->with(CustomerGroup::class)
            ->willReturn($repository);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(self::DEFAULT_GROUP_ID, false, false, $organization);

        $container = TestContainerBuilder::create()
            ->add('oro_config.manager', $configManager)
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
