<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Datagrid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Exception\LogicException;
use Oro\Bundle\FrontendBundle\Datagrid\Extension\FrontendDatagridExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendDatagridExtensionTest extends TestCase
{
    private FrontendHelper&MockObject $frontendHelper;
    private FrontendDatagridExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->extension = new FrontendDatagridExtension($this->frontendHelper);
        $this->extension->setParameters(new ParameterBag());
    }

    public function testShouldBeIsApplicableIfFrontendOptionIsNotSet(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);

        self::assertTrue($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldBeIsApplicableForBackendGrid(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => false]]);

        self::assertTrue($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldNotBeIsApplicableForFrontendGrid(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => true]]);

        self::assertFalse($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldGrantAccessForFrontendGrid(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => true]]);

        $this->extension->processConfigs($datagridConfig);
    }

    public function testShouldGrantAccessForBackendGridForBackendRequest(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->extension->processConfigs($datagridConfig);
    }

    public function testShouldDenyAccessForBackendGridForFrontendRequest(): void
    {
        $this->expectException(LogicException::class);
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->extension->processConfigs($datagridConfig);
    }
}
