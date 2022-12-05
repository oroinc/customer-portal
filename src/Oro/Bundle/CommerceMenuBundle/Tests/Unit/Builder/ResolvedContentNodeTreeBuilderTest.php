<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\Builder\ResolvedContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;
use Oro\Bundle\WebCatalogBundle\ContentNodeUtils\ContentNodeTreeResolverInterface;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Provider\RequestWebContentScopeProvider;
use Oro\Bundle\WebCatalogBundle\Tests\Unit\Stub\ContentNodeStub;

class ResolvedContentNodeTreeBuilderTest extends \PHPUnit\Framework\TestCase
{
    private RequestWebContentScopeProvider|\PHPUnit\Framework\MockObject\MockObject $requestWebContentScopeProvider;

    private ContentNodeTreeResolverInterface|\PHPUnit\Framework\MockObject\MockObject $contentNodeTreeResolver;

    private FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper;

    private ResolvedContentNodeTreeBuilder $builder;

    protected function setUp(): void
    {
        $this->requestWebContentScopeProvider = $this->createMock(RequestWebContentScopeProvider::class);
        $this->contentNodeTreeResolver = $this->createMock(ContentNodeTreeResolverInterface::class);
        $localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->builder = new ResolvedContentNodeTreeBuilder(
            $this->requestWebContentScopeProvider,
            $this->contentNodeTreeResolver,
            $localizationHelper,
            $this->frontendHelper
        );

        $localizationHelper
            ->expects(self::any())
            ->method('getLocalizedValue')
            ->willReturnCallback(static fn ($collection) => (string)($collection[0] ?? null));
    }

    public function testBuildWhenNotStorefront(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem
            ->expects(self::never())
            ->method(self::anything())
            ->willReturn(false);

        $this->contentNodeTreeResolver
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $contentNode = new ContentNode();

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(false);
        $menuItem->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode);

        $this->requestWebContentScopeProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->contentNodeTreeResolver
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoContentNode(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(true);

        $this->requestWebContentScopeProvider
            ->expects(self::never())
            ->method(self::anything());

        $this->contentNodeTreeResolver
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoScopes(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $contentNode = new ContentNode();

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(true);
        $menuItem->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode);

        $scopes = [];
        $this->requestWebContentScopeProvider
            ->expects(self::once())
            ->method('getScopes')
            ->willReturn($scopes);

        $this->contentNodeTreeResolver
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);

        self::assertFalse($menuItem->isDisplayed());
    }

    public function testBuildWhenNoChildren(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $contentNode = new ContentNodeStub(42);

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(true);
        $menuItem->addChild(
            'sample_menu_item',
            ['extras' => [MenuUpdate::TARGET_CONTENT_NODE => $contentNode]]
        );

        $scope1 = new Scope();
        $this->requestWebContentScopeProvider
            ->expects(self::once())
            ->method('getScopes')
            ->willReturn([$scope1]);

        $resolvedContentNode = $this->createResolvedNode($contentNode->getId(), 'Root');

        $this->contentNodeTreeResolver
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, [$scope1], ['tree_depth' => 0])
            ->willReturn($resolvedContentNode);

        $this->builder->build($menuItem);

        self::assertEquals(
            [
                'display' => true,
                'label' => 'sample_menu',
                'uri' => null,
                'extras' => [],
                'children' => [
                    'sample_menu_item' =>
                        [
                            'display' => true,
                            'label' => 'Root',
                            'uri' => 'node/42',
                            'extras' => [
                                'content_node' =>
                                    [
                                        'id' => 42,
                                    ],
                            ],
                            'children' => [],
                        ],
                ],
            ],
            $this->normalizeMenuItem($menuItem)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildWhenHasChildren(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $contentNode = new ContentNodeStub(42);

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(true);
        $menuItem->addChild(
            'sample_menu_item',
            ['extras' => [MenuUpdate::TARGET_CONTENT_NODE => $contentNode, 'max_traverse_level' => 5]]
        );

        $scope1 = new Scope();
        $this->requestWebContentScopeProvider
            ->expects(self::once())
            ->method('getScopes')
            ->willReturn([$scope1]);

        $resolvedContentNode = $this->createResolvedNode(
            $contentNode->getId(),
            'Root',
            [
                $this->createResolvedNode(11, 'Node 1'),
                $this->createResolvedNode(12, 'Node 2', [$this->createResolvedNode(121, 'Node 21')]),
                $this->createResolvedNode(13, 'Node 3'),
            ]
        );

        $this->contentNodeTreeResolver
            ->expects(self::once())
            ->method('getResolvedContentNode')
            ->with($contentNode, [$scope1], ['tree_depth' => 5])
            ->willReturn($resolvedContentNode);

        $this->builder->build($menuItem);

        self::assertEquals(
            [
                'display' => true,
                'label' => 'sample_menu',
                'uri' => null,
                'extras' => [],
                'children' => [
                    'sample_menu_item' => [
                        'display' => true,
                        'label' => 'Root',
                        'uri' => 'node/42',
                        'extras' => ['content_node' => ['id' => 42], 'max_traverse_level' => 5],
                        'children' => [
                            'sample_menu_item_11' => [
                                'display' => true,
                                'label' => 'Node 1',
                                'uri' => 'node/11',
                                'extras' => [
                                    'isAllowed' => true,
                                    'content_node' => ['id' => 11],
                                    'max_traverse_level' => 4,
                                ],
                                'children' => [],
                            ],
                            'sample_menu_item_12' => [
                                'display' => true,
                                'label' => 'Node 2',
                                'uri' => 'node/12',
                                'extras' => [
                                    'isAllowed' => true,
                                    'content_node' => ['id' => 12],
                                    'max_traverse_level' => 4,
                                ],
                                'children' => [
                                    'sample_menu_item_121' => [
                                        'display' => true,
                                        'label' => 'Node 21',
                                        'uri' => 'node/121',
                                        'extras' => [
                                            'isAllowed' => true,
                                            'content_node' => [
                                                'id' => 121,
                                            ],
                                            'max_traverse_level' => 3,
                                        ],
                                        'children' => [],
                                    ],
                                ],
                            ],
                            'sample_menu_item_13' => [
                                'display' => true,
                                'label' => 'Node 3',
                                'uri' => 'node/13',
                                'extras' => [
                                    'isAllowed' => true,
                                    'content_node' => ['id' => 13],
                                    'max_traverse_level' => 4,
                                ],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
            $this->normalizeMenuItem($menuItem)
        );
    }

    private function normalizeMenuItem(ItemInterface $menuItem): array
    {
        $result = [
            'display' => $menuItem->isDisplayed(),
            'label' => $menuItem->getLabel(),
            'uri' => $menuItem->getUri(),
            'extras' => $menuItem->getExtras(),
        ];

        if (isset($result['extras'][MenuUpdate::TARGET_CONTENT_NODE])) {
            $result['extras'][MenuUpdate::TARGET_CONTENT_NODE] = [
                'id' => $result['extras'][MenuUpdate::TARGET_CONTENT_NODE]->getId(),
            ];
        }

        $result['children'] = [];
        foreach ($menuItem->getChildren() as $childMenuItem) {
            $result['children'][$childMenuItem->getName()] = $this->normalizeMenuItem($childMenuItem);
        }

        return $result;
    }

    private function createResolvedNode(
        int $id,
        string $title,
        array $childNodes = []
    ): ResolvedContentNode {
        $resolvedNode = new ResolvedContentNode(
            $id,
            'sample_identifier_' . $id,
            $id,
            new ArrayCollection([(new LocalizedFallbackValue())->setString($title)]),
            (new ResolvedContentVariant())->addLocalizedUrl((new LocalizedFallbackValue())->setString('node/' . $id))
        );

        foreach ($childNodes as $childNode) {
            $resolvedNode->addChildNode($childNode);
        }

        return $resolvedNode;
    }
}
