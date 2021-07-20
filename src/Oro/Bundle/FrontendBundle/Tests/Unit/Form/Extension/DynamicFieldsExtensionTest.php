<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\FrontendBundle\Form\Extension\DynamicFieldsExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension\Stub\TestFormTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class DynamicFieldsExtensionTest extends FormIntegrationTestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $extendConfigProvider;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendConfigProvider;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var DynamicFieldsExtension */
    private $extension;

    /** @var FormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $formBuilder;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->extension = new DynamicFieldsExtension($this->frontendHelper, $this->configManager);

        parent::setUp();

        $this->formBuilder = new FormBuilder(null, \stdClass::class, new EventDispatcher(), $this->factory);

        $this->extendConfigProvider = $this->createMock(ConfigProvider::class);
        $this->frontendConfigProvider= $this->createMock(ConfigProvider::class);

        $this->configManager->expects($this->any())
            ->method('getProvider')
            ->willReturnMap(
                [
                    ['extend', $this->extendConfigProvider],
                    ['frontend', $this->frontendConfigProvider],
                ]
            );
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals([FormType::class], DynamicFieldsExtension::getExtendedTypes());
    }

    /**
     * @dataProvider buildFormDataProvider
     */
    public function testBuildForm(bool $isFrontendRequest, array $extend, array $frontend, bool $expected): void
    {
        $this->frontendHelper->expects($this->any())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->extendConfigProvider->expects($this->any())
            ->method('getConfig')
            ->willReturn(new Config(new FieldConfigId('extend', \stdClass::class, 'test'), $extend));

        $this->frontendConfigProvider->expects($this->any())
            ->method('getConfigs')
            ->willReturn(
                [
                    new Config(new FieldConfigId('frontend', \stdClass::class, 'test'), $frontend),
                    new Config(new FieldConfigId('frontend', \stdClass::class, 'unknown'), $frontend)
                ]
            );

        $form = $this->factory->create(TestFormTypeStub::class);

        $this->assertEquals($expected, $form->has('test'));
    }

    public function buildFormDataProvider(): array
    {
        return [
            [
                'isFrontendRequest' => true,
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'frontend' => ['is_editable' => false],
                'expected' => false
            ],
            [
                'isFrontendRequest' => true,
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'frontend' => ['is_editable' => false],
                'expected' => true
            ],
            [
                'isFrontendRequest' => true,
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'frontend' => ['is_editable' => true],
                'expected' => true
            ],
            [
                'isFrontendRequest' => false,
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'frontend' => ['is_editable' => false],
                'expected' => true
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([], [FormType::class => [$this->extension]])
        ];
    }
}
