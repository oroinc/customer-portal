<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Model;

use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class QuickAccessButtonConfigTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $properties = [
            ['type', 'test'],
            ['menu', 'testMenu'],
            ['webCatalogNode', 1],
        ];

        self::assertPropertyAccessors(new QuickAccessButtonConfig(), $properties);
    }

    public function testGetters()
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
}
