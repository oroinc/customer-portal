<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Doctrine\DoctrineFiltersListener;
use Oro\Bundle\CustomerBundle\Doctrine\SoftDeleteableFilter;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class DoctrineFiltersListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider onRequestDataProvider
     */
    public function testOnRequest(bool $isFrontEnd)
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $frontendHelper = $this->createMock(FrontendHelper::class);
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontEnd);

        if ($isFrontEnd) {
            $em = $this->createMock(EntityManager::class);
            $filterCollection = $this->createMock(FilterCollection::class);
            $filterCollection->expects($this->once())
                ->method('enable')
                ->willReturn(new SoftDeleteableFilter($em));

            $em->expects($this->once())
                ->method('getFilters')
                ->willReturn($filterCollection);

            $registry->expects($this->once())
                ->method('getManager')
                ->willReturn($em);
        }

        $listener = new DoctrineFiltersListener($registry, $frontendHelper);
        $listener->onRequest();
    }

    public function onRequestDataProvider(): array
    {
        return [
            'frontend request' => [
                'isFrontEnd' => true,
            ],
            'backend request' => [
                'isFrontEnd' => false,
            ]
        ];
    }
}
