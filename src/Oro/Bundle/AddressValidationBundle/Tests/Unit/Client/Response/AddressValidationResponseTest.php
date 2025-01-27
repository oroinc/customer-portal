<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Client\Response;

use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class AddressValidationResponseTest extends TestCase
{
    public function testGetters(): void
    {
        $resolvedAddresses = ['addresses'];
        $alerts = ['alerts'];
        $errors = ['test' => 'error'];

        $response = new AddressValidationResponse();

        self::assertEquals(Response::HTTP_OK, $response->getResponseStatusCode());
        self::assertTrue($response->isSuccessful());

        $response = new AddressValidationResponse(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $resolvedAddresses,
            $errors
        );

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getResponseStatusCode());
        self::assertEquals($resolvedAddresses, $response->getResolvedAddresses());
        self::assertEquals($errors, $response->getErrors());
        self::assertFalse($response->isSuccessful());
    }
}
