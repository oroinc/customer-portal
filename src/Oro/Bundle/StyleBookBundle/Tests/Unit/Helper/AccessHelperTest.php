<?php

namespace Oro\Bundle\StyleBookBundle\Tests\Unit\Helper;

use Oro\Bundle\StyleBookBundle\Helper\AccessHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AccessHelperTest extends TestCase
{
    /**
     * @dataProvider isAllowStyleBookProvider
     */
    public function testIsAllowStyleBook(bool|int $debug, bool $expected): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('getParameter')
            ->with('kernel.debug')
            ->willReturn($debug);

        $helper = new AccessHelper($container);

        $this->assertSame($expected, $helper->isAllowStyleBook());
    }

    public function isAllowStyleBookProvider(): array
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
