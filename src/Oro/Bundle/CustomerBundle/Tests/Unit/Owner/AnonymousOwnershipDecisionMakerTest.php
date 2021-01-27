<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Owner\AnonymousOwnershipDecisionMaker;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\CustomerVisitorOwnerAwareStub;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProvider;

class AnonymousOwnershipDecisionMakerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AnonymousOwnershipDecisionMaker */
    private $decisionMaker;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $metadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->decisionMaker = new AnonymousOwnershipDecisionMaker(
            $this->createMock(OwnerTreeProvider::class),
            new ObjectIdAccessor($this->createMock(DoctrineHelper::class)),
            new EntityOwnerAccessor($metadataProvider, (new InflectorFactory())->build()),
            $metadataProvider,
            $this->tokenAccessor
        );
    }

    /**
     * @dataProvider supportsDataProvider
     *
     * @param null| $token
     * @param bool $expectedResult
     */
    public function testSupports($token, $expectedResult)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals($expectedResult, $this->decisionMaker->supports());
    }

    /**
     * @dataProvider associatedDataProvider
     *
     * @param CustomerVisitor $tokenVisitor
     * @param mixed $object
     * @param bool $expectedResult
     */
    public function testIsAssociatedWithOrganization($tokenVisitor, $object, $expectedResult)
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->method('getVisitor')
            ->willReturn($tokenVisitor);
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(
            $expectedResult,
            $this->decisionMaker->isAssociatedWithOrganization(new \stdClass(), $object)
        );
    }

    /**
     * @dataProvider associatedDataProvider
     *
     * @param CustomerVisitor $tokenVisitor
     * @param mixed $object
     * @param bool $expectedResult
     */
    public function testIsAssociatedWithBusinessUnit($tokenVisitor, $object, $expectedResult)
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->method('getVisitor')
            ->willReturn($tokenVisitor);
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(
            $expectedResult,
            $this->decisionMaker->isAssociatedWithBusinessUnit(new \stdClass(), $object)
        );
    }

    /**
     * @dataProvider associatedDataProvider
     *
     * @param CustomerVisitor $tokenVisitor
     * @param mixed $object
     * @param bool $expectedResult
     */
    public function testIsAssociatedWithUser($tokenVisitor, $object, $expectedResult)
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->method('getVisitor')
            ->willReturn($tokenVisitor);
        $this->tokenAccessor->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertEquals(
            $expectedResult,
            $this->decisionMaker->isAssociatedWithBusinessUnit(new \stdClass(), $object)
        );
    }

    /**
     * @return array
     */
    public function supportsDataProvider()
    {
        return [
            'without token' => [
                'user' => null,
                'expectedResult' => false,
            ],
            'unsupported token' => [
                'user' => new \stdClass(),
                'expectedResult' => false,
            ],
            'supported token' => [
                'user' => new AnonymousCustomerUserToken(''),
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function associatedDataProvider()
    {
        $visitor = new CustomerVisitor();
        $visitor->setSessionId('session_id');

        return [
            'equal visitors' => [
                'tokenVisitor' => $visitor,
                'object' => new CustomerVisitorOwnerAwareStub($visitor),
                'expectedResult' => true,
            ],
            'not equal visitors' => [
                'tokenVisitor' => $visitor,
                'object' => new CustomerVisitorOwnerAwareStub(new CustomerVisitor()),
                'expectedResult' => false,
            ],
            'unsupported object' => [
                'tokenVisitor' => $visitor,
                'object' => new \stdClass(),
                'expectedResult' => false,
            ],
        ];
    }
}
