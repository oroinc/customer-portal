<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Doctrine\DoctrineFiltersListener;
use Oro\Bundle\CustomerBundle\Doctrine\SoftDeleteableFilter;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineFiltersListenerTest extends TestCase
{
    private ManagerRegistry&MockObject $doctrine;
    private FrontendHelper&MockObject $frontendHelper;
    private DoctrineFiltersListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->listener = new DoctrineFiltersListener($this->doctrine, $this->frontendHelper);
    }

    public function testOnRequestForBackendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->doctrine->expects(self::never())
            ->method('getManager');

        $this->listener->onRequest();
    }

    public function testOnRequestForFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $filterCollection = $this->createMock(FilterCollection::class);
        $filterCollection->expects(self::once())
            ->method('enable')
            ->willReturn(new SoftDeleteableFilter($em));
        $em->expects(self::once())
            ->method('getFilters')
            ->willReturn($filterCollection);

        $this->doctrine->expects(self::once())
            ->method('getManager')
            ->willReturn($em);

        $this->listener->onRequest();
    }
}
