<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\FrontendBundle\Form\Extension\DynamicFieldsExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension\Stub\TestFormTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
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

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->extension = new DynamicFieldsExtension($this->frontendHelper, $this->configManager);

        parent::setUp();

        $this->extendConfigProvider = $this->createMock(ConfigProvider::class);
        $this->frontendConfigProvider = $this->createMock(ConfigProvider::class);

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
                    new Config(new FieldConfigId('frontend', \stdClass::class, 'unknown'), $frontend),
                ]
            );

        $form = $this->factory->create(TestFormTypeStub::class, null, ['data_class' => CustomerUser::class]);

        $this->assertEquals($expected, $form->has('test'));
    }

    public function buildFormDataProvider(): array
    {
        return [
            [
                'isFrontendRequest' => true,
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'frontend' => ['is_editable' => false],
                'expected' => false,
            ],
            [
                'isFrontendRequest' => true,
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'frontend' => ['is_editable' => false],
                'expected' => true,
            ],
            [
                'isFrontendRequest' => true,
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'frontend' => ['is_editable' => true],
                'expected' => true,
            ],
            [
                'isFrontendRequest' => false,
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'frontend' => ['is_editable' => false],
                'expected' => true,
            ],
        ];
    }

    public function testBuildFormWhenNoDataClass(): void
    {
        $this->frontendHelper->expects(self::any())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->extendConfigProvider->expects(self::never())
            ->method(self::anything());

        $this->frontendConfigProvider->expects(self::never())
            ->method(self::anything());

        $form = $this->factory->create(TestFormTypeStub::class);

        self::assertTrue($form->has('test'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([], [FormType::class => [$this->extension]]),
        ];
    }
}
