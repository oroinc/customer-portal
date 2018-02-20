<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\Condition;

use Oro\Bundle\CommerceMenuBundle\Menu\Condition\LoggedInExpressionLanguageProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class LoggedInExpressionLanguageProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoggedInExpressionLanguageProvider */
    private $provider;

    /** @var TokenAccessorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $tokenAccessor;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->provider = new LoggedInExpressionLanguageProvider($this->tokenAccessor);
    }

    /**
     * @dataProvider getFunctionsDataProvider
     *
     * @param bool $isLoggedUser
     * @param bool $expectedData
     */
    public function testGetFunctions($isLoggedUser, $expectedData)
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

    /**
     * @return array
     */
    public function getFunctionsDataProvider()
    {
        return [
            [
                'isLoggedUser' => false,
                'expectedData' => false,
            ],
            [
                'isLoggedUser' => true,
                'expectedData' => true,
            ]
        ];
    }
}
