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

class OrmDatasourceAclListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var OwnershipMetadataProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $metadataProvider;

    /** @var OrmResultBefore|\PHPUnit\Framework\MockObject\MockObject */
    private $event;

    /** @var OrmDatasourceAclListener */
    private $listener;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->metadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);
        $this->event = $this->createMock(OrmResultBefore::class);

        $this->listener = new OrmDatasourceAclListener($this->tokenAccessor, $this->metadataProvider);
    }

    /**
     * @dataProvider onResultBeforeDataProvider
     */
    public function testOnResultBefore(array $entities = [], bool $expectedSkipAclCheck = true)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn(new CustomerUser());

        $from = $this->createMock(FromClause::class);

        foreach ($entities as $className => $hasOwner) {
            $rangeVariableDeclaration = $this->createMock(RangeVariableDeclaration::class);
            $rangeVariableDeclaration->abstractSchemaName = $className;
            $identVariableDeclaration = $this->createMock(IdentificationVariableDeclaration::class);
            $identVariableDeclaration->rangeVariableDeclaration = $rangeVariableDeclaration;

            $from->identificationVariableDeclarations[] = $identVariableDeclaration;
        }

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->addMethods(['getAST'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $select = $this->createMock(SelectStatement::class);
        $select->fromClause = $from;
        $query->expects($this->once())
            ->method('getAST')
            ->willReturn($select);

        $this->event->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->metadataProvider->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->willReturnCallback(function ($className) use ($entities) {
                $metadata = $this->createMock(OwnershipMetadataInterface::class);
                $metadata->expects($this->once())
                    ->method('hasOwner')
                    ->willReturn($entities[$className]);

                return $metadata;
            });

        $datagridConfiguration = $this->createMock(DatagridConfiguration::class);
        $datagridConfiguration->expects($expectedSkipAclCheck ? $this->once() : $this->never())
            ->method('offsetSetByPath')
            ->with(DatagridConfiguration::DATASOURCE_SKIP_ACL_APPLY_PATH, true)
            ->willReturnSelf();

        $datagrid = $this->createMock(DatagridInterface::class);
        $datagrid->expects($this->once())
            ->method('getConfig')
            ->willReturn($datagridConfiguration);

        $this->event->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $this->listener->onResultBefore($this->event);
    }

    public function onResultBeforeDataProvider(): array
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

        $this->event->expects($this->never())
            ->method('getQuery');
        $this->event->expects($this->never())
            ->method('getDatagrid');
        $this->metadataProvider->expects($this->never())
            ->method('getMetadata');

        $this->listener->onResultBefore($this->event);
    }
}
