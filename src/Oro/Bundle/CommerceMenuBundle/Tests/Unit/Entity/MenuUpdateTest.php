<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUserAgentCondition;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class MenuUpdateTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties(): void
    {
        $properties = [
            ['condition', 'condition'],
            ['screens', ['mobile' => ['class' => 'test']]],
            ['contentNode', new ContentNode(), false],
            ['systemPageRoute', 'sampleRoute', false],
            ['linkTarget', 0, 1],
            ['menuTemplate', 'list', false],
            ['depth', 2, false],
        ];

        self::assertPropertyAccessors(new MenuUpdate(), $properties);
    }

    public function testPropertiesCollections(): void
    {
        $properties = [
            ['menuUserAgentConditions', new MenuUserAgentCondition()],
        ];

        self::assertPropertyCollections(new MenuUpdate(), $properties);
    }

    public function testGetExtras(): void
    {
        $image = new File();
        $priority = 10;

        $update = new MenuUpdateStub();
        $screens = ['sample-screen'];
        $update
            ->setImage($image)
            ->setScreens($screens)
            ->setCondition('test condition')
            ->setIcon('test-icon')
            ->setPriority($priority)
            ->setDivider(true)
            ->setMenuTemplate('list')
            ->setDepth(3);

        $expected = [
            'image' => $image,
            'screens' => $screens,
            'condition' => 'test condition',
            'divider' => true,
            'userAgentConditions' => $update->getMenuUserAgentConditions(),
            'translate_disabled' => false,
            'position' => $priority,
            'icon' => 'test-icon',
            'menu_template' => 'list',
            'depth' => 3,
        ];

        self::assertSame($expected, $update->getExtras());
    }

    public function testGetExtraWhenContentNode(): void
    {
        $update = (new MenuUpdateStub())
            ->setContentNode($contentNode = $this->createMock(ContentNode::class))
            ->setSystemPageRoute('sample_route');

        $expected = [
            'image' => null,
            'screens' => [],
            'condition' => null,
            'divider' => false,
            'userAgentConditions' => $update->getMenuUserAgentConditions(),
            'translate_disabled' => false,
            'content_node' => $contentNode,
        ];

        self::assertSame($expected, $update->getExtras());
    }

    public function testGetExtraWhenSystemPageRoute(): void
    {
        $update = (new MenuUpdateStub())
            ->setSystemPageRoute($route = 'sample_route');

        $expected = [
            'image' => null,
            'screens' => [],
            'condition' => null,
            'divider' => false,
            'userAgentConditions' => $update->getMenuUserAgentConditions(),
            'translate_disabled' => false,
            'system_page_route' => $route,
        ];

        self::assertSame($expected, $update->getExtras());
    }

    public function testGetExtraWhenCategory(): void
    {
        $category = new Category();

        $update = (new MenuUpdateStub())
            ->setCategory($category);

        $expected = [
            'image' => null,
            'screens' => [],
            'condition' => null,
            'divider' => false,
            'userAgentConditions' => $update->getMenuUserAgentConditions(),
            'translate_disabled' => false,
            'category' => $category,
        ];

        self::assertSame($expected, $update->getExtras());
    }

    public function testGetLinkAttributes(): void
    {
        $update = new MenuUpdateStub();
        self::assertSame([], $update->getLinkAttributes());
        $update->setLinkTarget(0);
        self::assertSame(['target' => '_blank'], $update->getLinkAttributes());
    }

    /**
     * @dataProvider getTargetTypeDataProvider
     */
    public function testGetTargetType(MenuUpdate $menuUpdate, ?string $expectedTargetType): void
    {
        self::assertSame($expectedTargetType, $menuUpdate->getTargetType());
    }

    public function getTargetTypeDataProvider(): array
    {
        return [
            'uri target type' => [
                'menuUpdate' => (new MenuUpdateStub())->setUri('uri'),
                'expectedTargetType' => MenuUpdate::TARGET_URI,
            ],
            'system_page target type' => [
                'menuUpdate' => (new MenuUpdateStub())->setSystemPageRoute('sample_route'),
                'expectedTargetType' => MenuUpdate::TARGET_SYSTEM_PAGE,
            ],
            'content_node target type' => [
                'menuUpdate' => (new MenuUpdateStub())->setContentNode($this->createMock(ContentNode::class)),
                'expectedTargetType' => MenuUpdate::TARGET_CONTENT_NODE,
            ],
            'category target type' => [
                'menuUpdate' => (new MenuUpdateStub())->setCategory(new Category()),
                'expectedTargetType' => MenuUpdate::TARGET_CATEGORY,
            ],
            'no target type' => [
                'menuUpdate' => (new MenuUpdateStub()),
                'expectedTargetType' => null,
            ],
            'all target fields are filled' => [
                'menuUpdate' => (new MenuUpdateStub())
                    ->setUri('uri')
                    ->setSystemPageRoute('sample_route')
                    ->setContentNode($this->createMock(ContentNode::class)),
                'expectedTargetType' => 'content_node',
            ],
        ];
    }
}
