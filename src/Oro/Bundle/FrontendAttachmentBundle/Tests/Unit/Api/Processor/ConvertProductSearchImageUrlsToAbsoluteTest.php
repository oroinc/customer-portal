<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Provider\ApiUrlResolver;
use Oro\Bundle\FrontendAttachmentBundle\Api\Processor\ConvertProductSearchImageUrlsToAbsolute;
use Oro\Bundle\UIBundle\Tools\UrlHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConvertProductSearchImageUrlsToAbsoluteTest extends TestCase
{
    private UrlHelper&MockObject $urlHelper;

    private ApiUrlResolver&MockObject $apiUrlResolver;

    private ConvertProductSearchImageUrlsToAbsolute $processor;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = $this->createMock(UrlHelper::class);
        $this->apiUrlResolver = $this->createMock(ApiUrlResolver::class);

        $this->processor = new ConvertProductSearchImageUrlsToAbsolute(
            $this->urlHelper,
            $this->apiUrlResolver
        );
    }

    public function testProcessWhenAbsoluteUrlsNotRequired(): void
    {
        $context = new CustomizeLoadedDataContext();
        $data = [
            'text_image_product_large' => '/path/to/large.jpg',
            'text_image_product_medium' => '/path/to/medium.jpg',
        ];
        $context->setData($data);

        $this->apiUrlResolver->expects(self::once())
            ->method('shouldUseAbsoluteUrls')
            ->willReturn(false);

        $this->urlHelper->expects(self::never())
            ->method('getAbsoluteUrl');

        $this->processor->process($context);

        self::assertEquals($data, $context->getData());
    }

    public function testProcessConvertsRelativeUrlsToAbsolute(): void
    {
        $context = new CustomizeLoadedDataContext();
        $data = [
            'text_image_product_large' => '/path/to/large.jpg',
            'text_image_product_medium' => '/path/to/medium.jpg',
            'other_field' => 'value',
        ];
        $context->setData($data);

        $this->apiUrlResolver->expects(self::once())
            ->method('shouldUseAbsoluteUrls')
            ->willReturn(true);

        $this->urlHelper->expects(self::exactly(2))
            ->method('getAbsoluteUrl')
            ->willReturnMap([
                ['/path/to/large.jpg', 'https://example.com/path/to/large.jpg'],
                ['/path/to/medium.jpg', 'https://example.com/path/to/medium.jpg'],
            ]);

        $this->processor->process($context);

        $expectedData = [
            'text_image_product_large' => 'https://example.com/path/to/large.jpg',
            'text_image_product_medium' => 'https://example.com/path/to/medium.jpg',
            'other_field' => 'value',
        ];
        self::assertEquals($expectedData, $context->getData());
    }

    public function testProcessSkipsNonStringValues(): void
    {
        $context = new CustomizeLoadedDataContext();
        $data = [
            'text_image_product_large' => null,
            'text_image_product_medium' => 123,
        ];
        $context->setData($data);

        $this->apiUrlResolver->expects(self::once())
            ->method('shouldUseAbsoluteUrls')
            ->willReturn(true);

        $this->urlHelper->expects(self::never())
            ->method('getAbsoluteUrl');

        $this->processor->process($context);

        self::assertEquals($data, $context->getData());
    }

    public function testProcessSkipsMissingFields(): void
    {
        $context = new CustomizeLoadedDataContext();
        $data = [
            'other_field' => 'value',
        ];
        $context->setData($data);

        $this->apiUrlResolver->expects(self::once())
            ->method('shouldUseAbsoluteUrls')
            ->willReturn(true);

        $this->urlHelper->expects(self::never())
            ->method('getAbsoluteUrl');

        $this->processor->process($context);

        self::assertEquals($data, $context->getData());
    }

    public function testProcessDoesNotModifyDataWhenUrlsAlreadyAbsolute(): void
    {
        $context = new CustomizeLoadedDataContext();
        $data = [
            'text_image_product_large' => 'https://example.com/path/to/large.jpg',
            'text_image_product_medium' => 'https://example.com/path/to/medium.jpg',
        ];
        $context->setData($data);

        $this->apiUrlResolver->expects(self::once())
            ->method('shouldUseAbsoluteUrls')
            ->willReturn(true);

        $this->urlHelper->expects(self::exactly(2))
            ->method('getAbsoluteUrl')
            ->willReturnCallback(fn ($url) => $url);

        $this->processor->process($context);

        // Data should not be modified when URLs are already absolute
        self::assertEquals($data, $context->getData());
    }
}
