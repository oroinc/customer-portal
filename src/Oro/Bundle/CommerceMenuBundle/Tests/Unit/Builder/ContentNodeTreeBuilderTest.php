<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\Repository\ContentNodeRepository;
use Oro\Bundle\WebCatalogBundle\Tests\Unit\Stub\ContentNodeStub;

class ContentNodeTreeBuilderTest extends \PHPUnit\Framework\TestCase
{
    private FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper;

    private ContentNodeTreeBuilder $builder;

    private ContentNodeRepository|\PHPUnit\Framework\MockObject\MockObject $contentNodeRepo;

    protected function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->builder = new ContentNodeTreeBuilder(
            $managerRegistry,
            $localizationHelper,
            $this->frontendHelper
        );

        $this->contentNodeRepo = $this->createMock(ContentNodeRepository::class);
        $managerRegistry
            ->expects(self::any())
            ->method('getRepository')
            ->with(ContentNode::class)
            ->willReturn($this->contentNodeRepo);

        $localizationHelper
            ->expects(self::any())
            ->method('getLocalizedValue')
            ->willReturnCallback(static fn ($collection) => (string)($collection[0] ?? null));
    }

    public function testBuildWhenStorefront(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem
            ->expects(self::never())
            ->method(self::anything())
            ->willReturn(false);

        $this->contentNodeRepo
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNotDisplayed(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $contentNode = new ContentNode();

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(false);
        $menuItem->setExtra(MenuUpdate::TARGET_CONTENT_NODE, $contentNode);

        $this->contentNodeRepo
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoContentNode(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(true);

        $this->contentNodeRepo
            ->expects(self::never())
            ->method(self::anything());

        $this->builder->build($menuItem);
    }

    public function testBuildWhenNoChildren(): void
    {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $contentNode = $this->createContentNode(42, 'Root');

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(true);
        $menuItem->addChild(
            'sample_menu_item',
            ['extras' => [MenuUpdate::TARGET_CONTENT_NODE => $contentNode]]
        );

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->contentNodeRepo
            ->expects(self::once())
            ->method('getContentNodePlainTreeQueryBuilder')
            ->with($contentNode, 0)
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('addSelect')
            ->with('titles')
            ->willReturnSelf();

        $queryBuilder
            ->expects(self::once())
            ->method('innerJoin')
            ->with('node.titles', 'titles')
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $queryBuilder
            ->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);

        $contentNodes = [];
        $query
            ->expects(self::once())
            ->method('getResult')
            ->willReturn($contentNodes);

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
                            'uri' => null,
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
            ->willReturn(false);

        $contentNode11 = $this->createContentNode(11, 'Node 1');
        $contentNode121 = $this->createContentNode(121, 'Node 21');
        $contentNode12 = $this->createContentNode(12, 'Node 2', [$contentNode121]);
        $contentNode13 = $this->createContentNode(13, 'Node 3');
        $contentNode = $this->createContentNode(
            42,
            'Root',
            [$contentNode11, $contentNode12, $contentNode13]
        );

        $menuItem = new MenuItem('sample_menu', new MenuFactory());
        $menuItem->setDisplay(true);
        $menuItem->addChild(
            'sample_menu_item',
            ['extras' => [MenuUpdate::TARGET_CONTENT_NODE => $contentNode, 'max_traverse_level' => 5]]
        );

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $this->contentNodeRepo
            ->expects(self::once())
            ->method('getContentNodePlainTreeQueryBuilder')
            ->with($contentNode, 5)
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('addSelect')
            ->with('titles')
            ->willReturnSelf();

        $queryBuilder
            ->expects(self::once())
            ->method('innerJoin')
            ->with('node.titles', 'titles')
            ->willReturnSelf();

        $query = $this->createMock(AbstractQuery::class);
        $queryBuilder
            ->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);

        $contentNodes = [
            $contentNode11,
            $contentNode12,
            $contentNode121,
            $contentNode13,
        ];
        $query
            ->expects(self::once())
            ->method('getResult')
            ->willReturn($contentNodes);

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
                        'uri' => null,
                        'extras' => ['content_node' => ['id' => 42], 'max_traverse_level' => 5],
                        'children' => [
                            'sample_menu_item_11' => [
                                'display' => true,
                                'label' => 'Node 1',
                                'uri' => null,
                                'extras' => [
                                    'content_node' => ['id' => 11],
                                    'max_traverse_level' => 4,
                                ],
                                'children' => [],
                            ],
                            'sample_menu_item_12' => [
                                'display' => true,
                                'label' => 'Node 2',
                                'uri' => null,
                                'extras' => [
                                    'content_node' => ['id' => 12],
                                    'max_traverse_level' => 4,
                                ],
                                'children' => [
                                    'sample_menu_item_121' => [
                                        'display' => true,
                                        'label' => 'Node 21',
                                        'uri' => null,
                                        'extras' => [
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
                                'uri' => null,
                                'extras' => [
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

    private function createContentNode(
        int $id,
        string $title,
        array $childNodes = []
    ): ContentNode {
        $contentNode = new ContentNodeStub($id);
        $contentNode->addTitle((new LocalizedFallbackValue())->setString($title));

        foreach ($childNodes as $childNode) {
            $contentNode->addChildNode($childNode);
        }

        return $contentNode;
    }
}
