<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\FrontendBundle\Layout\DataProvider\ThemeCssVariablesConfigProvider;
use Oro\Bundle\FrontendBundle\Model\CssVariableConfig;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ThemeCssVariablesConfigProviderTest extends TestCase
{
    private ThemeConfigurationProvider&MockObject $themeConfigurationProvider;
    private ThemeCssVariablesConfigProvider $themeCssVariablesConfigProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);

        $this->themeCssVariablesConfigProvider = new ThemeCssVariablesConfigProvider(
            $this->themeConfigurationProvider,
            ['section1']
        );
    }

    /**
     * @dataProvider themeConfigurationDataProvider
     */
    public function testOutputStylesVariables(array $configurationOptions, array $expected): void
    {
        $this->themeConfigurationProvider->expects(self::any())
            ->method('getThemeConfigurationOptions')
            ->willReturn($configurationOptions);

        self::assertSame($expected, $this->themeCssVariablesConfigProvider->getStylesVariables());
    }

    private function themeConfigurationDataProvider(): array
    {
        $validConfig = new CssVariableConfig();
        $validConfig->setVariableName('color');
        $validConfig->setValue('red');

        return [
            'not applied section' => [
                ['not_applied_section2__item' => new CssVariableConfig()],
                []
            ],
            'valid section and empty inner value' => [
                ['section1__empty_inner_value' => new CssVariableConfig()],
                []
            ],
            'valid section and empty value' => [
                ['section1__empty_inner_value' => null],
                []
            ],
            'valid section and valid inner value' => [
                ['section1__valid_inner_value' => $validConfig],
                ['color' => 'red']
            ],
        ];
    }
}
