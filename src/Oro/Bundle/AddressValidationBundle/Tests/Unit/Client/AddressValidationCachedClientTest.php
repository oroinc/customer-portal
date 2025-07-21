<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Client;

use Oro\Bundle\AddressValidationBundle\Cache\AddressValidationCacheKey;
use Oro\Bundle\AddressValidationBundle\Cache\AddressValidationResponseCacheInterface;
use Oro\Bundle\AddressValidationBundle\Client\AddressValidationCachedClient;
use Oro\Bundle\AddressValidationBundle\Client\AddressValidationClientInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponse;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\Entity\TestTransport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class AddressValidationCachedClientTest extends TestCase
{
    private AddressValidationClientInterface&MockObject $client;
    private AddressValidationResponseCacheInterface&MockObject $cache;
    private AddressValidationCachedClient $cachedClient;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(AddressValidationClientInterface::class);
        $this->cache = $this->createMock(AddressValidationResponseCacheInterface::class);

        $this->cachedClient = new AddressValidationCachedClient($this->client, $this->cache);
    }

    public function testSendCachedResponse(): void
    {
        $request = $this->createMock(AddressValidationRequestInterface::class);
        $transport = new TestTransport();

        $this->cache->expects(self::once())
            ->method('get')
            ->with(new AddressValidationCacheKey($request, $transport))
            ->willReturn(new AddressValidationResponse());

        $this->client->expects(self::never())
            ->method('send')
            ->withAnyParameters();

        $response = $this->cachedClient->send($request, $transport);

        self::assertEquals(new AddressValidationResponse(), $response);
    }

    public function testSendNoSuccessfulResponse(): void
    {
        $request = $this->createMock(AddressValidationRequestInterface::class);
        $transport = new TestTransport();
        $expectedResponse = new AddressValidationResponse(Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->cache->expects(self::once())
            ->method('get')
            ->with(new AddressValidationCacheKey($request, $transport))
            ->willReturn(null);

        $this->client->expects(self::once())
            ->method('send')
            ->with($request, $transport)
            ->willReturn($expectedResponse);

        $response = $this->cachedClient->send($request, $transport);

        self::assertEquals($expectedResponse, $response);
    }

    public function testSend(): void
    {
        $request = $this->createMock(AddressValidationRequestInterface::class);
        $transport = new TestTransport();
        $cacheKey = new AddressValidationCacheKey($request, $transport);

        $this->cache->expects(self::once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn(null);

        $this->client->expects(self::once())
            ->method('send')
            ->with($request, $transport)
            ->willReturn(new AddressValidationResponse());

        $this->cache->expects(self::once())
            ->method('set')
            ->with($cacheKey, new AddressValidationResponse());

        $response = $this->cachedClient->send($request, $transport);

        self::assertEquals(new AddressValidationResponse(), $response);
    }
}
