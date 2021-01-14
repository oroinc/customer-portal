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
     *
     * @param bool $isFrontEnd
     */
    public function testOnRequest($isFrontEnd)
    {
        $registry = $this->getRegistryMock();

        $frontendHelper = $this->getFrontendHelperMock();
        $frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontEnd);

        if ($isFrontEnd) {
            $em = $this->getEmMock();
            $filterCollection = $this->getFilterCollectionMock();
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

    /**
     * @return array
     */
    public function onRequestDataProvider()
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

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected function getRegistryMock()
    {
        return $this->getMockBuilder('Doctrine\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityManager
     */
    protected function getEmMock()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|FrontendHelper
     */
    protected function getFrontendHelperMock()
    {
        return $this->getMockBuilder('Oro\Bundle\FrontendBundle\Request\FrontendHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|FilterCollection
     */
    protected function getFilterCollectionMock()
    {
        return $this->getMockBuilder('Doctrine\ORM\Query\FilterCollection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
