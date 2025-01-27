<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Cache;

use Oro\Bundle\AddressValidationBundle\Cache\AddressValidationCacheKey;
use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\Entity\TestTransport;
use PHPUnit\Framework\TestCase;

final class AddressValidationCacheKeyTest extends TestCase
{
    public function testGetters(): void
    {
        $request = $this->createMock(AddressValidationRequestInterface::class);
        $request->expects(self::exactly(2))
            ->method('getRequestData')
            ->willReturn(['test']);

        $transport = new TestTransport();

        $key = new AddressValidationCacheKey($request, $transport);

        self::assertEquals($request, $key->getRequest());
        self::assertEquals($transport, $key->getTransport());
        self::assertEquals((string) crc32(serialize($request->getRequestData())), $key->getCacheKey());
    }
}
