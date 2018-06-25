<?php

namespace Oro\Bundle\StyleBookBundle\Tests\Unit\Helper;

use Oro\Bundle\StyleBookBundle\Helper\AccessHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AccessHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $debug
     * @param bool $expected
     * @dataProvider isAllowStyleBookProvider
     */
    public function testIsAllowStyleBook($debug, $expected)
    {
        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('getParameter')
            ->with('kernel.debug')
            ->willReturn($debug);

        $helper = new AccessHelper($container);

        $this->assertSame($expected, $helper->isAllowStyleBook());
    }

    /**
     * @return array
     */
    public function isAllowStyleBookProvider()
    {
        return [
            'debug true' => [
                'debug' => true,
                'expected' => true,
            ],
            'debug false' => [
                'debug' => false,
                'expected' => false,
            ],
            'debug 1' => [
                'debug' => 1,
                'expected' => true,
            ],
            'debug 0' => [
                'debug' => 0,
                'expected' => false,
            ],
        ];
    }
}
