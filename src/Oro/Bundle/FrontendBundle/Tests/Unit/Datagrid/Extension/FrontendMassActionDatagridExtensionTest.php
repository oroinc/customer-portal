<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Datagrid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\FrontendBundle\Datagrid\Extension\FrontendMassActionDatagridExtension;
use PHPUnit\Framework\TestCase;

class FrontendMassActionDatagridExtensionTest extends TestCase
{
    private FrontendMassActionDatagridExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new FrontendMassActionDatagridExtension();
        $this->extension->setParameters(new ParameterBag());
    }

    public function testGetPriority(): void
    {
        self::assertEquals(210, $this->extension->getPriority());
    }

    public function testShouldNotBeIsApplicableIfFrontendOptionIsNotSet(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', []);

        self::assertFalse($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldNotBeIsApplicableForBackendGrid(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => false]]);

        self::assertFalse($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldBeIsApplicableForFrontendGrid(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed('test_grid', ['options' => ['frontend' => true]]);

        self::assertTrue($this->extension->isApplicable($datagridConfig));
    }

    public function testShouldSkipWhenNoMassActions(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed(
            'test_grid',
            ['options' => ['frontend' => true]]
        );

        self::assertNull($datagridConfig->offsetGetByPath('[mass_actions]'));

        $this->extension->visitMetadata($datagridConfig, $this->createMock(MetadataObject::class));

        self::assertEquals([], $datagridConfig->offsetGetByPath('[mass_actions]'));
    }

    public function testShouldAddFrontendFlagToMassAction(): void
    {
        $datagridConfig = DatagridConfiguration::createNamed(
            'test_grid',
            ['options' => ['frontend' => true], 'mass_actions' => ['sample_action' => ['type' => 'ajax']]]
        );

        self::assertNull($datagridConfig->offsetGetByPath('[mass_actions][sample_action][frontend]'));

        $this->extension->visitMetadata($datagridConfig, $this->createMock(MetadataObject::class));

        self::assertTrue($datagridConfig->offsetGetByPath('[mass_actions][sample_action][frontend]'));
    }
}
