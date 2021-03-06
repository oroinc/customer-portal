<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Oro\Bundle\WebsiteBundle\DependencyInjection\OroWebsiteExtension;

class OroWebsiteExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroWebsiteExtension());

        $this->assertExtensionConfigsLoaded([OroWebsiteExtension::ALIAS]);
    }

    public function testGetAlias()
    {
        $extension = new OroWebsiteExtension();
        $this->assertEquals(OroWebsiteExtension::ALIAS, $extension->getAlias());
    }
}
