<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Request;

use Oro\Bundle\FrontendBundle\Request\NotInstalledFrontendHelper;

class NotInstalledFrontendHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testIsFrontendRequestNotInstalled()
    {
        $helper = new NotInstalledFrontendHelper();
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendUrlForNotInstalled()
    {
        $helper = new NotInstalledFrontendHelper();
        $this->assertFalse($helper->isFrontendUrl('/test'));
    }
}
