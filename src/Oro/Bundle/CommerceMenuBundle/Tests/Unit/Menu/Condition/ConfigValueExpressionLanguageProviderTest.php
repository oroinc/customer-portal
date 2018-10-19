<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\Condition;

use Oro\Bundle\CommerceMenuBundle\Menu\Condition\ConfigValueExpressionLanguageProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class ConfigValueExpressionLanguageProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigValueExpressionLanguageProvider */
    private $provider;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)->disableOriginalConstructor()->getMock();
        $this->provider = new ConfigValueExpressionLanguageProvider($this->configManager);
    }

    public function testGetFunctions()
    {
        $functions = $this->provider->getFunctions();
        $this->assertCount(1, $functions);

        /** @var ExpressionFunction $function */
        $function = array_shift($functions);

        $paramName = 'param.name';

        $this->assertInstanceOf(ExpressionFunction::class, $function);
        $this->assertEquals(
            sprintf('config_value(%s)', $paramName),
            call_user_func($function->getCompiler(), $paramName)
        );
        $this->assertNull(call_user_func($function->getEvaluator(), [], $paramName));

        $configValue = 'config_value';
        $this->configManager->expects($this->once())
            ->method('get')
            ->with($paramName)
            ->willReturn($configValue);

        $this->assertEquals($configValue, call_user_func($function->getEvaluator(), [], $paramName));
    }
}
