<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Acl\Voter\CustomerGroupVoter;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CustomerGroupVoterTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    const DEFAULT_GROUP_ID = 1;

    /**
     * @var CustomerGroupVoter
     */
    protected $voter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->willReturnCallback(function ($group) {
                return $group instanceof CustomerGroup ? $group->getId() : null;
            });

        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->expects($this->any())
            ->method('get')
            ->with('oro_customer.anonymous_customer_group')
            ->willReturn(self::DEFAULT_GROUP_ID);

        $this->voter = new CustomerGroupVoter($doctrineHelper);
        $this->voter->setClassName('Oro\Bundle\CustomerBundle\Entity\CustomerGroup');
        $this->voter->setConfigManager($configManager);
    }

    /**
     * @dataProvider voteDataProvider
     * @param object $object
     * @param int $result
     * @param string $attribute
     */
    public function testVote($object, $result, $attribute)
    {
        $this->assertSame($result, $this->voter->vote($this->getToken(), $object, [$attribute]));
    }

    /**
     * @return array
     */
    public function voteDataProvider()
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

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|TokenInterface
     */
    protected function getToken()
    {
        return $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param int $id
     * @return CustomerGroup
     */
    protected function getGroup($id)
    {
        return $this->getEntity('Oro\Bundle\CustomerBundle\Entity\CustomerGroup', ['id' => $id]);
    }
}
