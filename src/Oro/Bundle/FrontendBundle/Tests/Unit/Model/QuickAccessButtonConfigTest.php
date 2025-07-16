<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Model;

use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class QuickAccessButtonConfigTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors(): void
    {
        $properties = [
            ['type', 'test'],
            ['menu', 'testMenu'],
            ['webCatalogNode', 1],
        ];

        self::assertPropertyAccessors(new QuickAccessButtonConfig(), $properties);
    }

    public function testGetters(): void
    {
        $config = new QuickAccessButtonConfig();
        // check default values
        self::assertNull($config->getType());
        self::assertNull($config->getMenu());
        self::assertNull($config->getWebCatalogNode());

        $config->setType('type');
        $config->setMenu('menu');
        $config->setWebCatalogNode(1);

        $this->assertSame('type', $config->getType());
        $this->assertSame('menu', $config->getMenu());
        $this->assertSame(1, $config->getWebCatalogNode());
    }

    /**
     * @dataProvider clearConfigDataProvider
     */
    public function testClearConfig(?string $type, QuickAccessButtonConfig $expected): void
    {
        $config = new QuickAccessButtonConfig();
        $config->setWebCatalogNode(1)
            ->setMenu('frontend_menu')
            ->setLabel(['default' => 'string'])
            ->setType($type);

        $config->clearConfig();

        self::assertEquals($expected, $config);
    }

    public function clearConfigDataProvider(): array
    {
        return [
            'none type is empty string' => [
                'type' => '',
                'expected' => (new QuickAccessButtonConfig())->setType(''),
            ],
            'none type is null' => [
                'type' => null,
                'expected' => new QuickAccessButtonConfig(),
            ],
            'menu type' => [
                'type' => 'menu',
                'expected' => (new QuickAccessButtonConfig())
                    ->setType('menu')
                    ->setMenu('frontend_menu')
                    ->setLabel(['default' => 'string']),
            ],
            'web_catalog_node type' => [
                'type' => 'web_catalog_node',
                'expected' => (new QuickAccessButtonConfig())
                    ->setType('web_catalog_node')
                    ->setWebCatalogNode(1)
                    ->setLabel(['default' => 'string']),
            ],
        ];
    }
}
