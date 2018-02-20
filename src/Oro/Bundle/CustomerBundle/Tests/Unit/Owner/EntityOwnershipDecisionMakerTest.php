<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\EntityOwnershipDecisionMaker;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;

class EntityOwnershipDecisionMakerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OwnershipMetadataProviderInterface
     */
    protected $metadataProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OwnerTreeProvider
     */
    protected $treeProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityOwnershipDecisionMaker
     */
    protected $decisionMaker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenAccessorInterface
     */
    protected $tokenAccessor;

    protected function setUp()
    {
        $this->treeProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataProvider = $this
            ->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrine = $this->createMock(ManagerRegistry::class);

        $this->decisionMaker = new EntityOwnershipDecisionMaker(
            $this->treeProvider,
            new ObjectIdAccessor($doctrineHelper),
            new EntityOwnerAccessor($this->metadataProvider),
            $this->metadataProvider,
            $this->tokenAccessor,
            $doctrine
        );
    }

    /**
     * @dataProvider supportsDataProvider
     *
     * @param mixed $user
     * @param bool $expectedResult
     */
    public function testSupports($user, $expectedResult)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expectedResult, $this->decisionMaker->supports());
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
    {
        return [
            'without user' => [
                'user' => null,
                'expectedResult' => false,
            ],
            'unsupported user' => [
                'user' => new \stdClass(),
                'expectedResult' => false,
            ],
            'supported user' => [
                'user' => new CustomerUser(),
                'expectedResult' => true,
            ],
        ];
    }
}
