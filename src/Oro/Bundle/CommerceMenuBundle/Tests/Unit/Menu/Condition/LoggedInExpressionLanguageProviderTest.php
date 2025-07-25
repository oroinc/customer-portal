<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\Condition;

use Oro\Bundle\CommerceMenuBundle\Menu\Condition\LoggedInExpressionLanguageProvider;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class LoggedInExpressionLanguageProviderTest extends TestCase
{
    private LoggedInExpressionLanguageProvider $provider;
    private TokenAccessorInterface&MockObject $tokenAccessor;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->provider = new LoggedInExpressionLanguageProvider($this->tokenAccessor);
    }

    /**
     * @dataProvider getFunctionsDataProvider
     */
    public function testGetFunctions(bool $isLoggedUser, bool $expectedData): void
    {
        $functions = $this->provider->getFunctions();
        $this->assertCount(1, $functions);

        /** @var ExpressionFunction $function */
        $function = array_shift($functions);

        $this->assertInstanceOf(ExpressionFunction::class, $function);
        $this->assertEquals('is_logged_in()', call_user_func($function->getCompiler()));

        $this->tokenAccessor->expects($this->once())
            ->method('hasUser')
            ->willReturn($isLoggedUser);

        $this->assertEquals($expectedData, call_user_func($function->getEvaluator()));
    }

    public function getFunctionsDataProvider(): array
    {
        return [
            ['isLoggedUser' => false, 'expectedData' => false],
            ['isLoggedUser' => true, 'expectedData' => true]
        ];
    }
}
