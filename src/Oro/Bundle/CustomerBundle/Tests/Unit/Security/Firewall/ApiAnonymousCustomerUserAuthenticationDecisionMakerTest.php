<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\ApiBundle\Processor\ActionProcessorBagInterface;
use Oro\Bundle\ApiBundle\Processor\Options\OptionsContext;
use Oro\Bundle\ApiBundle\Provider\ConfigProvider;
use Oro\Bundle\ApiBundle\Provider\MetadataProvider;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\ErrorCompleterInterface;
use Oro\Bundle\ApiBundle\Request\ErrorCompleterRegistry;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutes;
use Oro\Bundle\CustomerBundle\Security\Firewall\ApiAnonymousCustomerUserAuthenticationDecisionMaker;
use Oro\Component\ChainProcessor\ActionProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAnonymousCustomerUserAuthenticationDecisionMakerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ActionProcessorBagInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $actionProcessorBag;

    /** @var ErrorCompleterRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $errorCompleterRegistry;

    /** @var ApiAnonymousCustomerUserAuthenticationDecisionMaker */
    private $decisionMaker;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->actionProcessorBag = $this->createMock(ActionProcessorBagInterface::class);
        $this->errorCompleterRegistry = $this->createMock(ErrorCompleterRegistry::class);

        $this->decisionMaker = new ApiAnonymousCustomerUserAuthenticationDecisionMaker(
            ['Test\Entity2'],
            $this->actionProcessorBag,
            new RestRoutes('item_route', 'list_route', 'subresource_route', 'relationship_route'),
            $this->errorCompleterRegistry
        );
    }

    private function getOptionsContext(): OptionsContext
    {
        return new OptionsContext(
            $this->createMock(ConfigProvider::class),
            $this->createMock(MetadataProvider::class)
        );
    }

    public function testIsAnonymousCustomerUserAllowedWhenNoEntityTypeInRequest(): void
    {
        $request = Request::create('http://example.com');

        $this->actionProcessorBag->expects(self::never())
            ->method('getProcessor');

        $this->errorCompleterRegistry->expects(self::never())
            ->method('getErrorCompleter');

        self::assertFalse($this->decisionMaker->isAnonymousCustomerUserAllowed($request));
    }

    public function testIsAnonymousCustomerUserAllowedWhenEntityClassCannotBeRetrieved(): void
    {
        $request = Request::create('http://example.com');
        $request->attributes->set('_route', 'list_route');
        $request->attributes->set('entity', 'testEntity');

        $processor = $this->createMock(ActionProcessorInterface::class);
        $context = $this->getOptionsContext();
        $this->actionProcessorBag->expects(self::once())
            ->method('getProcessor')
            ->with(ApiAction::OPTIONS)
            ->willReturn($processor);
        $processor->expects(self::once())
            ->method('createContext')
            ->willReturn($context);
        $processor->expects(self::once())
            ->method('process')
            ->with(self::identicalTo($context))
            ->willReturnCallback(function (OptionsContext $context) {
                $context->addError(Error::create('some error'));
            });

        $errorCompleter = $this->createMock(ErrorCompleterInterface::class);
        $this->errorCompleterRegistry->expects(self::once())
            ->method('getErrorCompleter')
            ->willReturn($errorCompleter);
        $errorCompleter->expects(self::once())
            ->method('complete')
            ->willReturnCallback(function (Error $error) {
                $error->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            });

        self::assertFalse($this->decisionMaker->isAnonymousCustomerUserAllowed($request));
    }

    public function testIsAnonymousCustomerUserAllowedWhenApiResourceDisabled(): void
    {
        $request = Request::create('http://example.com');
        $request->attributes->set('_route', 'list_route');
        $request->attributes->set('entity', 'testEntity');

        $processor = $this->createMock(ActionProcessorInterface::class);
        $context = $this->getOptionsContext();
        $this->actionProcessorBag->expects(self::once())
            ->method('getProcessor')
            ->with(ApiAction::OPTIONS)
            ->willReturn($processor);
        $processor->expects(self::once())
            ->method('createContext')
            ->willReturn($context);
        $processor->expects(self::once())
            ->method('process')
            ->with(self::identicalTo($context))
            ->willReturnCallback(function (OptionsContext $context) {
                $context->addError(Error::create('some error'));
            });

        $errorCompleter = $this->createMock(ErrorCompleterInterface::class);
        $this->errorCompleterRegistry->expects(self::once())
            ->method('getErrorCompleter')
            ->willReturn($errorCompleter);
        $errorCompleter->expects(self::once())
            ->method('complete')
            ->willReturnCallback(function (Error $error) {
                $error->setStatusCode(Response::HTTP_NOT_FOUND);
            });

        self::assertTrue($this->decisionMaker->isAnonymousCustomerUserAllowed($request));
    }

    public function testIsAnonymousCustomerUserAllowedWhenNotAllowed(): void
    {
        $request = Request::create('http://example.com');
        $request->attributes->set('_route', 'list_route');
        $request->attributes->set('entity', 'testEntity');

        $processor = $this->createMock(ActionProcessorInterface::class);
        $context = $this->getOptionsContext();
        $this->actionProcessorBag->expects(self::once())
            ->method('getProcessor')
            ->with(ApiAction::OPTIONS)
            ->willReturn($processor);
        $processor->expects(self::once())
            ->method('createContext')
            ->willReturn($context);
        $processor->expects(self::once())
            ->method('process')
            ->with(self::identicalTo($context))
            ->willReturnCallback(function (OptionsContext $context) {
                $context->setClassName('Test\Entity1');
            });

        $this->errorCompleterRegistry->expects(self::never())
            ->method('getErrorCompleter');

        self::assertFalse($this->decisionMaker->isAnonymousCustomerUserAllowed($request));
    }

    /**
     * @dataProvider isAnonymousCustomerUserAllowedWhenAllowedDataProvider
     */
    public function testIsAnonymousCustomerUserAllowedWhenAllowed(?string $route, string $actionType): void
    {
        $request = Request::create('http://example.com');
        if (null !== $route) {
            $request->attributes->set('_route', $route);
        }
        $request->attributes->set('entity', 'testEntity');

        $processor = $this->createMock(ActionProcessorInterface::class);
        $context = $this->getOptionsContext();
        $this->actionProcessorBag->expects(self::once())
            ->method('getProcessor')
            ->with(ApiAction::OPTIONS)
            ->willReturn($processor);
        $processor->expects(self::once())
            ->method('createContext')
            ->willReturn($context);
        $processor->expects(self::once())
            ->method('process')
            ->with(self::identicalTo($context))
            ->willReturnCallback(function (OptionsContext $context) use ($actionType) {
                self::assertEquals($actionType, $context->getActionType());
                $context->setClassName('Test\Entity2');
            });

        $this->errorCompleterRegistry->expects(self::never())
            ->method('getErrorCompleter');

        self::assertTrue($this->decisionMaker->isAnonymousCustomerUserAllowed($request));
    }

    public static function isAnonymousCustomerUserAllowedWhenAllowedDataProvider(): array
    {
        return [
            [null, OptionsContext::ACTION_TYPE_LIST],
            ['item_route', OptionsContext::ACTION_TYPE_ITEM],
            ['list_route', OptionsContext::ACTION_TYPE_LIST],
            ['subresource_route', OptionsContext::ACTION_TYPE_ITEM],
            ['relationship_route', OptionsContext::ACTION_TYPE_ITEM],
            ['another_route', OptionsContext::ACTION_TYPE_LIST],
        ];
    }
}
