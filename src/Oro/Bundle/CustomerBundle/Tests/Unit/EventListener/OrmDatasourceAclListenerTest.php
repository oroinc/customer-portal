<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\AST\FromClause;
use Doctrine\ORM\Query\AST\IdentificationVariableDeclaration;
use Doctrine\ORM\Query\AST\RangeVariableDeclaration;
use Doctrine\ORM\Query\AST\SelectStatement;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\OrmDatasourceAclListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;

class OrmDatasourceAclListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|TokenAccessorInterface
     */
    protected $tokenAccessor;

    /**
     * @var MockObject|OwnershipMetadataProviderInterface
     */
    protected $metadataProvider;

    /**
     * @var OrmDatasourceAclListener
     */
    protected $listener;

    /**
     * @var MockObject|OrmResultBefore
     */
    protected $event;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->metadataProvider = $this
            ->createMock('Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface');

        $this->listener = new OrmDatasourceAclListener($this->tokenAccessor, $this->metadataProvider);

        $this->event = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Event\OrmResultBefore')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        unset($this->authorizationChecker, $this->metadataProvider, $this->listener, $this->event);
    }

    /**
     * @dataProvider onResultBeforeDataProvider
     *
     * @param array $entities
     * @param bool $expectedSkipAclCheck
     */
    public function testOnResultBefore($entities = [], $expectedSkipAclCheck = true)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        /** @var FromClause $from */
        $from = $this->getMockBuilder('Doctrine\ORM\Query\AST\FromClause')->disableOriginalConstructor()->getMock();

        foreach ($entities as $className => $hasOwner) {
            $from->identificationVariableDeclarations[] = $this->createIdentVariableDeclarationMock($className);
        }

        $this->event->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->createQueryMock($from));

        $this->metadataProvider->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->willReturnCallback(
                function ($className) use ($entities) {
                    return $this->createOwnershipMetadataMock($entities[$className]);
                }
            );

        $this->event->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($this->createDatagridMock($expectedSkipAclCheck));

        $this->listener->onResultBefore($this->event);
    }

    /**
     * @return array
     */
    public function onResultBeforeDataProvider()
    {
        return [
            [
                'entities' => [
                    '\stdClass' . mt_rand() => true,
                    '\stdClass' . mt_rand() => true
                ],
                'expectedSkipAclCheck' => false
            ],
            [
                'entities' => [
                    '\stdClass' . mt_rand() => true,
                    '\stdClass' . mt_rand() => false
                ],
                'expectedSkipAclCheck' => false
            ],
            [
                'entities' => [
                    '\stdClass' . mt_rand() => false,
                    '\stdClass' . mt_rand() => true
                ],
                'expectedSkipAclCheck' => false
            ],
            [
                'entities' => [
                    '\stdClass' . mt_rand() => false,
                    '\stdClass' . mt_rand() => false
                ],
                'expectedSkipAclCheck' => true
            ],
            [
                'entities' => [
                    '\stdClass' . mt_rand() => false
                ],
                'expectedSkipAclCheck' => true
            ],
            [
                'entities' => [
                    '\stdClass' . mt_rand() => true
                ],
                'expectedSkipAclCheck' => false
            ]
        ];
    }

    public function testOnResultBeforeSkipForBackendUser()
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(new User());

        $this->event->expects($this->never())->method('getQuery');
        $this->event->expects($this->never())->method('getDatagrid');
        $this->metadataProvider->expects($this->never())->method('getMetadata');

        $this->listener->onResultBefore($this->event);
    }

    /**
     * @param bool $expectedSkipAclCheck
     * @return DatagridInterface|MockObject
     */
    protected function createDatagridMock($expectedSkipAclCheck)
    {
        /** @var MockObject|DatagridConfiguration $datagridConfiguration */
        $datagridConfiguration = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $datagridConfiguration->expects($expectedSkipAclCheck ? $this->once() : $this->never())
            ->method('offsetSetByPath')
            ->with(DatagridConfiguration::DATASOURCE_SKIP_ACL_APPLY_PATH, true)
            ->willReturnSelf();

        /** @var MockObject|DatagridInterface $datagrid */
        $datagrid = $this->createMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');
        $datagrid->expects($this->once())
            ->method('getConfig')
            ->willReturn($datagridConfiguration);

        return $datagrid;
    }

    /**
     * @param FromClause $from
     * @return AbstractQuery|MockObject
     */
    protected function createQueryMock(FromClause $from)
    {
        /** @var MockObject|SelectStatement $select */
        $select = $this->getMockBuilder('Doctrine\ORM\Query\AST\SelectStatement')
            ->disableOriginalConstructor()
            ->getMock();
        $select->fromClause = $from;

        /** @var MockObject|AbstractQuery $query */
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(['getAST'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('getAST')
            ->willReturn($select);

        return $query;
    }

    /**
     * @param string $className
     * @return IdentificationVariableDeclaration|MockObject
     */
    protected function createIdentVariableDeclarationMock($className)
    {
        /** @var MockObject|RangeVariableDeclaration $rangeVariableDeclaration */
        $rangeVariableDeclaration = $this->getMockBuilder('Doctrine\ORM\Query\AST\RangeVariableDeclaration')
            ->disableOriginalConstructor()
            ->getMock();
        $rangeVariableDeclaration->abstractSchemaName = $className;

        /** @var MockObject|IdentificationVariableDeclaration $identVariableDeclaration */
        $identVariableDeclaration = $this->getMockBuilder('Doctrine\ORM\Query\AST\IdentificationVariableDeclaration')
            ->disableOriginalConstructor()
            ->getMock();
        $identVariableDeclaration->rangeVariableDeclaration = $rangeVariableDeclaration;

        return $identVariableDeclaration;
    }

    /**
     * @param bool $hasOwner
     * @return OwnershipMetadataInterface|MockObject
     */
    protected function createOwnershipMetadataMock($hasOwner)
    {
        /** @var MockObject|OwnershipMetadataInterface $metadata */
        $metadata = $this->createMock('Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataInterface');
        $metadata->expects($this->once())
            ->method('hasOwner')
            ->willReturn($hasOwner);

        return $metadata;
    }
}
