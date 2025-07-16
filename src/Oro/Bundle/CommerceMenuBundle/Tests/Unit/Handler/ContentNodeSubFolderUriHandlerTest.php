<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CommerceMenuBundle\Handler\ContentNodeSubFolderUriHandler;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RequestContext;

class ContentNodeSubFolderUriHandlerTest extends TestCase
{
    private LocalizationHelper&MockObject $localizationHelper;
    private RequestContext&MockObject $requestContext;
    private ContentNodeSubFolderUriHandler $handler;

    #[\Override]
    protected function setUp(): void
    {
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->requestContext = $this->createMock(RequestContext::class);

        $this->handler = new ContentNodeSubFolderUriHandler(
            $this->localizationHelper,
            $this->requestContext
        );
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(string $baseUrl, string $expected): void
    {
        $fallbackValue = new LocalizedFallbackValue();
        $fallbackValue->setString('/main_page');

        $resolvedContentVariant = $this->createMock(ResolvedContentVariant::class);
        $resolvedContentVariant->expects(self::once())
            ->method('getLocalizedUrls')
            ->willReturn(new ArrayCollection([$fallbackValue]));

        $resolvedNode = $this->createMock(ResolvedContentNode::class);
        $resolvedNode->expects(self::once())
            ->method('getResolvedContentVariant')
            ->willReturn($resolvedContentVariant);

        $this->localizationHelper->expects(self::once())
            ->method('getLocalizedValue')
            ->willReturn($fallbackValue);

        $this->requestContext->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        self::assertEquals($expected, $this->handler->handle($resolvedNode, new Localization()));
    }

    public function handleDataProvider(): array
    {
        return [
            'with base url' => ['/sub_folder', '/sub_folder/main_page'],
            'without base url' => ['', '/main_page'],
        ];
    }
}
