<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Oro\Bundle\CommerceMenuBundle\Builder\NavigationRootBuilder;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Stub\ScopeStub;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\NavigationBundle\Provider\MenuUpdateProvider;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class NavigationRootBuilderTest extends \PHPUnit\Framework\TestCase
{
    use MenuItemTestTrait;

    private WebCatalogProvider $webCatalogProvider;

    private BuilderInterface $masterCatalogNavigationRootBuilder;

    private BuilderInterface $webCatalogNavigationRootBuilder;

    private NavigationRootBuilder $builder;

    protected function setUp(): void
    {
        $this->webCatalogProvider = $this->createMock(WebCatalogProvider::class);
        $this->masterCatalogNavigationRootBuilder = $this->createMock(BuilderInterface::class);
        $this->webCatalogNavigationRootBuilder = $this->createMock(BuilderInterface::class);

        $this->builder = new NavigationRootBuilder(
            $this->webCatalogProvider,
            $this->masterCatalogNavigationRootBuilder,
            $this->webCatalogNavigationRootBuilder
        );
    }

    public function testBuildWhenTargetMenuNotSet(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->masterCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $this->webCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    public function testBuildWhenTargetMenuNotEquals(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->masterCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $this->webCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $this->builder->setTargetMenuName('target_menu');
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $this->webCatalogProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->masterCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $this->webCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $menu = $this->createItem('sample_menu');
        $menu->setDisplay(false);
        $this->builder->setTargetMenuName($menu->getName());
        $this->builder->build($menu);

        self::assertEmpty($menu->getChildren());
    }

    /**
     * @dataProvider getBuildItemHasWebCatalogDataProvider
     */
    public function testBuildWhenHasWebCatalog(array $options, ?Website $expectedWebsite): void
    {
        $menu = $this->createItem('sample_menu');

        $webCatalog = new WebCatalog();
        $this->webCatalogProvider->expects(self::once())
            ->method('getWebCatalog')
            ->with($expectedWebsite)
            ->willReturn($webCatalog);

        $this->masterCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $this->webCatalogNavigationRootBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menu, ['website' => $expectedWebsite] + $options);

        $this->builder->setTargetMenuName($menu->getName());
        $this->builder->build($menu, $options);

        self::assertEmpty($menu->getChildren());
    }

    public function getBuildItemHasWebCatalogDataProvider(): array
    {
        $website = new Website();

        return [
            'no website' => [
                'options' => [],
                'expectedWebsite' => null,
            ],
            'not website from [scopeContext][website]' => [
                'options' => [
                    MenuUpdateProvider::SCOPE_CONTEXT_OPTION => ['website' => new \stdClass()],
                ],
                'expectedWebsite' => null,
            ],
            'website from [scopeContext][website]' => [
                'options' => [
                    MenuUpdateProvider::SCOPE_CONTEXT_OPTION => ['website' => $website],
                ],
                'expectedWebsite' => $website,
            ],
            'website from [scopeContext] as Scope entity' => [
                'options' => [
                    MenuUpdateProvider::SCOPE_CONTEXT_OPTION => (new ScopeStub())->setWebsite($website),
                ],
                'expectedWebsite' => $website,
            ],
        ];
    }

    /**
     * @dataProvider getBuildItemHasWebCatalogDataProvider
     */
    public function testBuildWhenNoWebCatalog(array $options, ?Website $expectedWebsite): void
    {
        $menu = $this->createItem('sample_menu');

        $this->webCatalogProvider
            ->expects(self::once())
            ->method('getWebCatalog')
            ->willReturn(null);

        $this->masterCatalogNavigationRootBuilder
            ->expects(self::once())
            ->method('build')
            ->with($menu, ['website' => $expectedWebsite] + $options);

        $this->webCatalogNavigationRootBuilder
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->setTargetMenuName($menu->getName());
        $this->builder->build($menu, $options);

        self::assertEmpty($menu->getChildren());
    }
}
