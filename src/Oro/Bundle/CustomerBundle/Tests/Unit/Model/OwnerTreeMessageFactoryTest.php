<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Model;

use Oro\Bundle\CustomerBundle\Model\OwnerTreeMessageFactory;
use Oro\Bundle\ProductBundle\Model\Exception\InvalidArgumentException;

class OwnerTreeMessageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OwnerTreeMessageFactory
     */
    private $messageFactory;

    protected function setUp(): void
    {
        $this->messageFactory = new OwnerTreeMessageFactory();
    }

    public function testGetCacheTtlWithValidValue(): void
    {
        $cacheTtl = 103;
        $data = $this->messageFactory->createMessage($cacheTtl);

        $this->assertEquals($cacheTtl, $this->messageFactory->getCacheTtl($data));
    }

    /**
     * @dataProvider wrongCacheTtlDataProvider
     */
    public function testGetCacheTtlWithWrongValue(int $cacheTtl): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->messageFactory->createMessage($cacheTtl);
    }

    public function wrongCacheTtlDataProvider(): array
    {
        return [
            'negative value' => [
                'cacheTtl' => -10,
            ],
            'zero value' => [
                'cacheTtl' => 0,
            ]
        ];
    }
}
