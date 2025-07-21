<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\Group;

use Oro\Bundle\CustomerBundle\Acl\Group\AclGroupProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AclGroupProviderTest extends TestCase
{
    private FrontendHelper&MockObject $frontendHelper;
    private AclGroupProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new AclGroupProvider($this->frontendHelper);
    }

    public function testSupportsForFrontend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->assertTrue($this->provider->supports());
    }

    public function testSupportsForBackend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->assertFalse($this->provider->supports());
    }

    public function testGetGroup(): void
    {
        $this->assertEquals(CustomerUser::SECURITY_GROUP, $this->provider->getGroup());
    }
}
