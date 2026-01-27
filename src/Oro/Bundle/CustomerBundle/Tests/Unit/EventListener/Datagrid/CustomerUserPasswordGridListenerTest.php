<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\EventListener\Datagrid\CustomerUserPasswordGridListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserPasswordGridListenerTest extends TestCase
{
    private FeatureChecker&MockObject $featureChecker;
    private CustomerUserPasswordGridListener $userPasswordGridListener;

    protected function setUp(): void
    {
        $this->featureChecker = $this->createMock(FeatureChecker::class);
        $this->userPasswordGridListener = new CustomerUserPasswordGridListener($this->featureChecker);
    }

    public function testOnBuildAfterWithEnabledFeature(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $event = new BuildAfter($datagrid);

        $datagrid->expects(self::never())
            ->method('getConfig');

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_login_password')
            ->willReturn(true);

        $this->userPasswordGridListener->onBuildAfter($event);
    }

    public function testOnBuildAfterWithDisabledFeature(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $config = $this->createMock(DatagridConfiguration::class);
        $event = new BuildAfter($datagrid);

        $datagrid->expects(self::once())
            ->method('getConfig')
            ->willReturn($config);

        $config->expects(self::once())
            ->method('removeColumn')
            ->with('auth_status');

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('customer_user_login_password')
            ->willReturn(false);

        $this->userPasswordGridListener->onBuildAfter($event);
    }
}
