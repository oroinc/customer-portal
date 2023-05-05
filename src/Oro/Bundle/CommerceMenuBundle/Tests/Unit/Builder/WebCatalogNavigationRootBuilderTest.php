<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Builder\WebCatalogNavigationRootBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Menu\ConfigurationBuilder;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Component\Website\WebsiteInterface;

class WebCatalogNavigationRootBuilderTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;
    use MenuItemTestTrait;

    private const MENU_TEMPLATE = 'template1';

    private WebCatalogProvider|\PHPUnit\Framework\MockObject\MockObject $webCatalogProvider;

    private WebCatalogNavigationRootBuilder $builder;

    protected function setUp(): void
    {
        $this->webCatalogProvider = $this->createMock(WebCatalogProvider::class);
        $menuTemplatesProvider = $this->createMock(MenuTemplatesProvider::class);

        $this->builder = new WebCatalogNavigationRootBuilder(
            $this->webCatalogProvider,
            $menuTemplatesProvider
        );

        $this->setUpLoggerMock($this->builder);
        $menuTemplatesProvider
            ->expects(self::any())
            ->method('getMenuTemplates')
            ->willReturn([
                self::MENU_TEMPLATE => ['label' => 'Template 1'],
                'template2' => ['label' => 'Template 2'],
            ]);
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(false);
        $this->builder->build($menu);

        self::assertNull($menu->getExtra(MenuUpdate::TARGET_CONTENT_NODE));
    }

    public function testBuildWhenInvalidWebsite(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method(self::anything());

        $website = new \stdClass();
        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(
                'Option "website" with value {actual_type} is expected to be {expected_type}',
                ['actual_type' => get_debug_type($website), 'expected_type' => WebsiteInterface::class]
            );

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu, ['website' => $website]);

        self::assertNull($menu->getExtra(MenuUpdate::TARGET_CONTENT_NODE));
    }

    public function testBuildWhenNoRootContentNode(): void
    {
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->willReturn(null);

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        self::assertNull($menu->getExtra(MenuUpdate::TARGET_CONTENT_NODE));
    }

    public function testBuildWhenHasRootContentNodeAndNoMaxTraverseLevel(): void
    {
        $rootContentNode = (new ContentNode())
            ->setWebCatalog((new WebCatalog())->setName('Sample Catalog'));
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->willReturn($rootContentNode);

        $menu = $this->createItem('sample_menu');
        $menu->setExtra(ConfigurationBuilder::MAX_NESTING_LEVEL, 6);
        $this->builder->build($menu);

        self::assertSame($rootContentNode, $menu->getExtra(MenuUpdate::TARGET_CONTENT_NODE));
        self::assertEquals(6, $menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL));
        self::assertEquals(
            ['extras' => [MenuUpdate::MENU_TEMPLATE => self::MENU_TEMPLATE]],
            $menu->getExtra(ContentNodeTreeBuilder::TREE_ITEM_OPTIONS)
        );
    }

    public function testBuildWhenHasRootContentNodeAndMaxTraverseLevel(): void
    {
        $rootContentNode = (new ContentNode())
            ->setWebCatalog((new WebCatalog())->setName('Sample Catalog'));
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->willReturn($rootContentNode);

        $menu = $this->createItem('sample_menu');
        $menu->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3);
        $this->builder->build($menu);

        self::assertSame($rootContentNode, $menu->getExtra(MenuUpdate::TARGET_CONTENT_NODE));
        self::assertEquals(3, $menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL));
        self::assertEquals(
            ['extras' => [MenuUpdate::MENU_TEMPLATE => self::MENU_TEMPLATE]],
            $menu->getExtra(ContentNodeTreeBuilder::TREE_ITEM_OPTIONS)
        );
    }

    public function testBuildWhenHasRootContentNodeAndTreeItemOptions(): void
    {
        $rootContentNode = (new ContentNode())
            ->setWebCatalog((new WebCatalog())->setName('Sample Catalog'));
        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getNavigationRootWithCatalogRootFallback')
            ->willReturn($rootContentNode);

        $menu = $this->createItem('sample_menu');
        $menu->setExtra(MenuUpdate::MAX_TRAVERSE_LEVEL, 3);
        $treeItemOptions = ['sample_key' => 'sample_value'];
        $this->builder->setTreeItemOptions($treeItemOptions);
        $this->builder->build($menu);

        self::assertSame($rootContentNode, $menu->getExtra(MenuUpdate::TARGET_CONTENT_NODE));
        self::assertEquals(3, $menu->getExtra(MenuUpdate::MAX_TRAVERSE_LEVEL));
        self::assertEquals(
            $treeItemOptions + ['extras' => [MenuUpdate::MENU_TEMPLATE => self::MENU_TEMPLATE]],
            $menu->getExtra(ContentNodeTreeBuilder::TREE_ITEM_OPTIONS)
        );
    }
}
