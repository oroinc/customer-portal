<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class DebugStorefrontRoutesCommandTest extends WebTestCase
{
    public function testCommandOutput(): void
    {
        $result = self::runCommand('oro:debug:storefront-routes', [], false, true);
        self::assertStringContainsString('- oro_frontend_root', $result);
    }
}
