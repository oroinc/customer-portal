<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Oro\Bundle\SaleBundle\Form\Handler\QuoteCustomerDataRequestHandler;
use Oro\Bundle\SaleBundle\Model\QuoteRequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class QuoteCustomerDataRequestHandlerTest extends TestCase
{
    private RequestStack&MockObject $requestStack;
    private QuoteRequestHandler&MockObject $quoteRequestHandler;
    private QuoteCustomerDataRequestHandler $quoteCustomerDataRequestHandler;

    #[\Override]
    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->quoteRequestHandler = $this->createMock(QuoteRequestHandler::class);

        $this->quoteCustomerDataRequestHandler = new QuoteCustomerDataRequestHandler(
            $this->requestStack,
            $this->quoteRequestHandler
        );
    }

    public function testThatCustomerDataProvided(): void
    {
        $customer = new Customer();
        $customerUser = new CustomerUser();
        $quote = new Quote();

        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->requestStack->expects(self::any())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->quoteRequestHandler->expects(self::any())
            ->method('getCustomer')
            ->willReturn($customer);
        $this->quoteRequestHandler->expects(self::any())
            ->method('getCustomerUser')
            ->willReturn($customerUser);

        $this->quoteCustomerDataRequestHandler->handle($quote);

        self::assertEquals($customer, $quote->getCustomer());
        self::assertEquals($customerUser, $quote->getCustomerUser());
    }

    public function testThatCustomerDataNotProvided(): void
    {
        $quote = new Quote();

        $request = $this->createMock(Request::class);
        $request->expects(self::any())
            ->method('getMethod')
            ->willReturn('GET');

        $this->requestStack->expects(self::any())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->quoteRequestHandler->expects(self::never())
            ->method('getCustomer');
        $this->quoteRequestHandler->expects(self::never())
            ->method('getCustomerUser');

        $this->quoteCustomerDataRequestHandler->handle($quote);
    }
}
