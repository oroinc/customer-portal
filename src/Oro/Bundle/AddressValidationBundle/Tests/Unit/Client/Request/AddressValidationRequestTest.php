<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Client\Request;

use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequest;
use PHPUnit\Framework\TestCase;

final class AddressValidationRequestTest extends TestCase
{
    public function testGetters(): void
    {
        $uri = 'test/uri';
        $data = ['1', '2'];

        $request = new AddressValidationRequest($uri, $data);

        self::assertEquals($uri, $request->getUri());
        self::assertEquals($data, $request->getRequestData());
        self::assertFalse($request->isCheckMode());

        $request = new AddressValidationRequest($uri, $data, true);

        self::assertTrue($request->isCheckMode());
    }
}
