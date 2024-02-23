<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Checker;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\ContentNodeDeletionChecker\ContentNodeInConfigReferencesChecker;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\WebCatalogBundle\Context\NotDeletableContentNodeResult;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentNodeInConfigReferencesCheckerTest extends TestCase
{
    private TranslatorInterface $translator;
    private ConfigManager $configManager;
    private WebsiteProviderInterface $websiteProvider;
    private ContentNode $contentNode;

    private ContentNodeInConfigReferencesChecker $checker;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->websiteProvider = $this->createMock(WebsiteProviderInterface::class);
        $this->contentNode = $this->createMock(ContentNode::class);

        $this->websiteProvider
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn([]);

        $this->contentNode
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->checker = new ContentNodeInConfigReferencesChecker(
            $this->translator,
            $this->configManager,
            $this->websiteProvider
        );
    }

    public function testWarningMessageParamsPassed()
    {
        $configValue = $this->createMock(QuickAccessButtonConfig::class);
        $configValue->expects(self::once())->method('getType')->willReturn('web_catalog_node');
        $configValue->expects(self::once())->method('getWebCatalogNode')->willReturn(1);

        $this->configManager
            ->expects($this->once())
            ->method('getValues')
            ->with(
                $this->equalTo('oro_frontend.quick_access_button'),
                $this->equalTo([]),
                $this->equalTo(false),
                $this->equalTo(true),
            )
            ->willReturn([
                [
                    'value' => $configValue,
                ],
            ]);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->equalTo('oro.webcatalog.system_configuration.label'));

        $this->assertInstanceOf(
            NotDeletableContentNodeResult::class,
            $this->checker->check($this->contentNode)
        );
    }

    public function testThatEmptyWarningMessageParamsReturns()
    {
        $configValue = $this->createMock(QuickAccessButtonConfig::class);
        $configValue->expects(self::once())->method('getType')->willReturn('web_catalog_node');
        $configValue->expects(self::once())->method('getWebCatalogNode')->willReturn(2);

        $this->configManager
            ->expects($this->once())
            ->method('getValues')
            ->with(
                $this->equalTo('oro_frontend.quick_access_button'),
                $this->equalTo([]),
                $this->equalTo(false),
                $this->equalTo(true),
            )
            ->willReturn([
                [
                    'value' => $configValue,
                ],
            ]);

        $this->translator
            ->expects($this->never())
            ->method('trans');

        $this->assertNull($this->checker->check($this->contentNode));
    }
}
