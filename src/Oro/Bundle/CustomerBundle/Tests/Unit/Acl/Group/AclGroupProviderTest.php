<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Group;

use Oro\Bundle\CustomerBundle\Acl\Group\AclGroupProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class AclGroupProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var AclGroupProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new AclGroupProvider($this->frontendHelper);
    }

    public function testSupportsForFrontend()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->assertTrue($this->provider->supports());
    }

    public function testSupportsForBackend()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->assertFalse($this->provider->supports());
    }

    public function testGetGroup()
    {
        $this->assertEquals(CustomerUser::SECURITY_GROUP, $this->provider->getGroup());
    }
}
