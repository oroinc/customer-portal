<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Api\Processor\Menu;

use Knp\Menu\MenuFactory;
use Oro\Bundle\ApiBundle\Filter\FilterValue;
use Oro\Bundle\ApiBundle\Filter\FilterValueAccessor;
use Oro\Bundle\ApiBundle\Processor\GetList\GetListContext;
use Oro\Bundle\ApiBundle\Provider\ConfigProvider;
use Oro\Bundle\ApiBundle\Provider\MetadataProvider;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\AttachmentBundle\Provider\FileUrlProviderInterface;
use Oro\Bundle\CommerceMenuBundle\Api\Model\CommerceMenuItem;
use Oro\Bundle\CommerceMenuBundle\Api\Processor\Menu\LoadMenuProcessor;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Layout\DataProvider\MenuProvider;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LoadMenuProcessorTest extends TestCase
{
    private MenuProvider&MockObject $menuProvider;
    private TranslatorInterface&MockObject $translator;
    private AttachmentManager&MockObject $attachmentManager;
    private ConfigProvider&MockObject $configProvider;
    private MetadataProvider&MockObject $metadataProvider;
    private LoadMenuProcessor $processor;
    private GetListContext $context;
    private MenuFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->menuProvider = $this->createMock(MenuProvider::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->attachmentManager = $this->createMock(AttachmentManager::class);

        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->metadataProvider = $this->createMock(MetadataProvider::class);
        $this->processor = new LoadMenuProcessor(
            $this->menuProvider,
            $this->translator,
            $this->attachmentManager
        );
        $this->context = new GetListContext($this->configProvider, $this->metadataProvider);
        $this->factory = new MenuFactory();
    }

    public function testProcessWhenResultAlreadyExists(): void
    {
        $existingResult = [new CommerceMenuItem(name: 'test', label: 'label', uri: '/test', description: null)];
        $this->context->setResult($existingResult);

        $this->menuProvider->expects(self::never())
            ->method('getMenu');

        $this->processor->process($this->context);

        self::assertSame($existingResult, $this->context->getResult());
    }

    /**
     * @dataProvider menuFilterNotProvidedOrEmptyDataProvider
     */
    public function testProcessWhenMenuFilterNotProvidedOrEmpty(FilterValueAccessor $filterValues): void
    {
        $this->context->setFilterValues($filterValues);

        $rootMenuItem = $this->factory->createItem('root');

        $this->menuProvider->expects(self::once())
            ->method('getMenu')
            ->with('frontend_menu')
            ->willReturn($rootMenuItem);

        $this->processor->process($this->context);

        self::assertEquals([], $this->context->getResult());
    }

    public function menuFilterNotProvidedOrEmptyDataProvider(): array
    {
        $emptyFilterValues = new FilterValueAccessor();
        $emptyFilterValues->set('menu', new FilterValue('menu', ''));

        return [
            'menu filter not provided' => [new FilterValueAccessor()],
            'menu filter is empty' => [$emptyFilterValues],
        ];
    }

    public function testProcessWhenMenuHasNoChildren(): void
    {
        $menuName = 'test_menu';
        $filterValues = new FilterValueAccessor();
        $filterValues->set('menu', new FilterValue('menu', $menuName));
        $this->context->setFilterValues($filterValues);

        $rootMenuItem = $this->factory->createItem('root');

        $this->menuProvider->expects(self::once())
            ->method('getMenu')
            ->with($menuName)
            ->willReturn($rootMenuItem);

        $this->processor->process($this->context);

        self::assertEquals([], $this->context->getResult());
    }

    public function testProcessWithDepthFilter(): void
    {
        $menuName = 'test_menu';
        $filterValues = new FilterValueAccessor();
        $filterValues->set('menu', new FilterValue('menu', $menuName));
        $filterValues->set('depth', new FilterValue('depth', '1'));
        $this->context->setFilterValues($filterValues);

        $rootMenuItem = $this->factory->createItem('root');
        $childItem1 = $this->factory->createItem('item1', ['uri' => '/item1', 'label' => 'Item 1']);
        $childItem2 = $this->factory->createItem('item2', ['uri' => '/item2', 'label' => 'Item 2']);
        $childItem1_1 = $this->factory->createItem('item1_1', ['uri' => '/item1_1', 'label' => 'Item 1-1']);
        $childItem1_2 = $this->factory->createItem('item1_2', ['uri' => '/item1_2', 'label' => 'Item 1-2']);
        $rootMenuItem->addChild($childItem1);
        $rootMenuItem->addChild($childItem2);
        $childItem1->addChild($childItem1_1);
        $childItem1->addChild($childItem1_2);

        $this->menuProvider->expects(self::once())
            ->method('getMenu')
            ->with($menuName)
            ->willReturn($rootMenuItem);

        $this->translator->expects(self::any())
            ->method('trans')
            ->willReturnArgument(0);

        $this->processor->process($this->context);

        $result = $this->context->getResult();
        self::assertIsArray($result);
        self::assertCount(2, $result);
    }

    /**
     * @dataProvider unlimitedDepthDataProvider
     */
    public function testProcessWithUnlimitedDepth(FilterValueAccessor $filterValues): void
    {
        $menuName = 'test_menu';
        $this->context->setFilterValues($filterValues);

        $rootMenuItem = $this->factory->createItem('root');
        $childItem1 = $this->factory->createItem('item1', ['uri' => '/item1', 'label' => 'Item 1']);
        $childItem2 = $this->factory->createItem('item2', ['uri' => '/item2', 'label' => 'Item 2']);
        $childItem1_1 = $this->factory->createItem('item1_1', ['uri' => '/item1_1', 'label' => 'Item 1-1']);
        $childItem1_2 = $this->factory->createItem('item1_2', ['uri' => '/item1_2', 'label' => 'Item 1-2']);
        $rootMenuItem->addChild($childItem1);
        $rootMenuItem->addChild($childItem2);
        $childItem1->addChild($childItem1_1);
        $childItem1->addChild($childItem1_2);

        $this->menuProvider->expects(self::once())
            ->method('getMenu')
            ->with($menuName)
            ->willReturn($rootMenuItem);

        $this->translator->expects(self::any())
            ->method('trans')
            ->willReturnArgument(0);

        $this->processor->process($this->context);

        $result = $this->context->getResult();
        self::assertIsArray($result);
        self::assertCount(4, $result);
    }

    public function unlimitedDepthDataProvider(): array
    {
        $menuName = 'test_menu';

        $filterValuesWithoutDepth = new FilterValueAccessor();
        $filterValuesWithoutDepth->set('menu', new FilterValue('menu', $menuName));

        $filterValuesWithZeroDepth = new FilterValueAccessor();
        $filterValuesWithZeroDepth->set('menu', new FilterValue('menu', $menuName));
        $filterValuesWithZeroDepth->set('depth', new FilterValue('depth', '0'));

        return [
            'without depth filter' => [$filterValuesWithoutDepth],
            'with unlimited depth (depth=0)' => [$filterValuesWithZeroDepth],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProcessWithMixedItems(): void
    {
        $menuName = 'test_menu';
        $filterValues = new FilterValueAccessor();
        $filterValues->set('menu', new FilterValue('menu', $menuName));
        $this->context->setFilterValues($filterValues);

        $rootMenuItem = $this->factory->createItem('root');

        $validItem = $this->factory->createItem('valid_item', ['uri' => '/valid', 'label' => 'Valid Item']);
        $rootMenuItem->addChild($validItem);

        $notAllowedItem = $this->factory->createItem(
            'not_allowed',
            ['uri' => '/not-allowed', 'label' => 'Not Allowed']
        );
        $notAllowedItem->setExtra('isAllowed', false);
        $rootMenuItem->addChild($notAllowedItem);

        $hiddenItem = $this->factory->createItem('hidden', ['uri' => '/hidden', 'label' => 'Hidden Item']);
        $hiddenItem->setDisplay(false);
        $rootMenuItem->addChild($hiddenItem);

        $itemWithTranslation = $this->factory->createItem(
            'item_translated',
            ['uri' => '/translated', 'label' => 'menu.item.translated']
        );
        $rootMenuItem->addChild($itemWithTranslation);

        $itemWithoutTranslation = $this->factory->createItem(
            'item_localized',
            ['uri' => '/localized', 'label' => 'Already Localized']
        );
        $itemWithoutTranslation->setExtra(MenuUpdateInterface::IS_CUSTOM, true);
        $rootMenuItem->addChild($itemWithoutTranslation);

        $itemWithDescription = $this->factory->createItem(
            'item_with_description',
            ['uri' => '/with-description', 'label' => 'Item With Description']
        );
        $itemWithDescription->setExtra(MenuUpdateInterface::DESCRIPTION, 'Menu item description text');
        $rootMenuItem->addChild($itemWithDescription);

        $itemWithDescriptionKey = $this->factory->createItem(
            'item_with_description_key',
            ['uri' => '/with-desc-key', 'label' => 'Item With Desc Key']
        );
        $itemWithDescriptionKey->setExtra(
            MenuUpdateInterface::DESCRIPTION,
            'oro.menu.item.description.key'
        );
        $rootMenuItem->addChild($itemWithDescriptionKey);

        $itemWithNullLabel = $this->factory->createItem(
            'item_null_label',
            ['uri' => '/null-label', 'label' => null]
        );
        $rootMenuItem->addChild($itemWithNullLabel);

        $itemWithLinkAttributes = $this->factory->createItem(
            'item_with_link_attrs',
            ['uri' => '/link-attrs', 'label' => 'Item With Link Attributes']
        );
        $itemWithLinkAttributes->setLinkAttributes(['target' => '_blank']);
        $rootMenuItem->addChild($itemWithLinkAttributes);

        $itemWithExtras = $this->factory->createItem(
            'item_with_extras',
            ['uri' => '/with-extras', 'label' => 'Item With Extras']
        );
        $itemWithExtras->setExtra(MenuUpdateInterface::IS_TRANSLATE_DISABLED, true);
        $file = $this->createMock(File::class);
        $itemWithExtras->setExtra(MenuUpdate::IMAGE, $file);
        $contentNode = $this->createMock(ContentNode::class);
        $itemWithExtras->setExtra('content_node', $contentNode);
        $itemWithExtras->setExtra(MenuUpdate::MENU_TEMPLATE, 'list');
        $rootMenuItem->addChild($itemWithExtras);

        $this->menuProvider->expects(self::once())
            ->method('getMenu')
            ->with($menuName)
            ->willReturn($rootMenuItem);

        $translatedLabel = 'Translated Label';
        $translatedDescription = 'Translated description text';
        $translationMap = [
            'menu.item.translated' => $translatedLabel,
            'oro.menu.item.description.key' => $translatedDescription,
        ];
        $this->translator->expects(self::atLeast(1))
            ->method('trans')
            ->willReturnCallback(function ($id) use ($translationMap) {
                return $translationMap[$id] ?? $id;
            });

        $imageUrl = 'https://example.com/image.jpg';
        $this->attachmentManager->expects(self::once())
            ->method('getFileUrl')
            ->with(
                $file,
                FileUrlProviderInterface::FILE_ACTION_GET,
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($imageUrl);

        $this->processor->process($this->context);

        $result = $this->context->getResult();
        self::assertIsArray($result);
        self::assertCount(8, $result, 'Only valid items should be returned');

        $resultMap = [];
        foreach ($result as $item) {
            $resultMap[$item->getName()] = $item;
        }

        self::assertEquals('valid_item', $resultMap['valid_item']->getName());
        self::assertNull($resultMap['valid_item']->getDescription());

        $item = $resultMap['item_translated'];
        self::assertEquals($translatedLabel, $item->getLabel());

        $item = $resultMap['item_localized'];
        self::assertEquals('Already Localized', $item->getLabel());

        $item = $resultMap['item_with_description'];
        self::assertEquals('Item With Description', $item->getLabel());
        self::assertEquals('Menu item description text', $item->getDescription());

        $item = $resultMap['item_with_description_key'];
        self::assertEquals('Item With Desc Key', $item->getLabel());
        self::assertEquals($translatedDescription, $item->getDescription());

        $item = $resultMap['item_null_label'];
        self::assertEquals('item_null_label', $item->getLabel());

        $item = $resultMap['item_with_link_attrs'];
        self::assertEquals('Item With Link Attributes', $item->getLabel());
        self::assertEquals(['target' => '_blank'], $item->getLinkAttributes());

        $item = $resultMap['item_with_extras'];
        self::assertEquals('Item With Extras', $item->getLabel());
        self::assertEquals($imageUrl, $item->getExtras()['image']);
        self::assertSame($contentNode, $item->getContentNode());
        self::assertEquals('list', $item->getExtras()['menu_template']);
    }
}
