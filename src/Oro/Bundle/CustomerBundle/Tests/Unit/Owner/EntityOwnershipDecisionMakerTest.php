<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\EntityOwnershipDecisionMaker;

class EntityOwnershipDecisionMakerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OwnershipMetadataProvider
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
     * @var ContainerInterface
     */
    protected $container;

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
            ->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'oro_security.ownership_tree_provider',
                            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                            $this->treeProvider,
                        ],
                        [
                            'oro_security.owner.metadata_provider.chain',
                            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                            $this->metadataProvider,
                        ],
                        [
                            'oro_security.token_accessor',
                            ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                            $this->tokenAccessor,
                        ],
                    ]
                )
            );

        $this->decisionMaker = new EntityOwnershipDecisionMaker();
        $this->decisionMaker->setContainer($this->container);
    }

    protected function tearDown()
    {
        unset(
            $this->metadataProvider,
            $this->treeProvider,
            $this->decisionMaker,
            $this->container,
            $this->tokenAccessor
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
