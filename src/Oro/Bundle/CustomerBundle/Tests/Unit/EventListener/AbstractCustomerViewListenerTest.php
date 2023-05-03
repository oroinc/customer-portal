<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\AbstractCustomerViewListener;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractCustomerViewListenerTest extends \PHPUnit\Framework\TestCase
{
    protected const RENDER_HTML = 'render_html';
    protected const TRANSLATED_TEXT = 'translated_text';

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $translator;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $doctrineHelper;

    /** @var Environment|\PHPUnit\Framework\MockObject\MockObject */
    protected $env;

    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestStack;

    /** @var Request|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var BeforeListRenderEvent|\PHPUnit\Framework\MockObject\MockObject */
    protected $event;

    /** @var AbstractCustomerViewListener */
    protected $customerViewListener;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($id) {
                return $id . '.trans';
            });

        $this->env = $this->createMock(Environment::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->request = $this->createMock(Request::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->event = new BeforeListRenderEvent(
            $this->env,
            new ScrollData(),
            new \stdClass()
        );

        $this->customerViewListener = $this->createListenerToTest();
    }

    public function testOnCustomerViewGetsIgnoredIfNoRequest()
    {
        $this->requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->customerViewListener->onCustomerView($this->event);
    }

    public function testOnCustomerViewGetsIgnoredIfNoRequestId()
    {
        $this->customerViewListener->onCustomerView($this->event);
    }

    public function testOnCustomerViewGetsIgnoredIfNoEntityFound()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->willReturn(1);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->willReturn(null);

        $this->customerViewListener->onCustomerView($this->event);
    }

    public function testOnCustomerViewCreatesScrollBlock()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->willReturn(1);

        $customer = new Customer();

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->with(Customer::class, 1)
            ->willReturn($customer);

        $this->env->expects($this->once())
            ->method('render')
            ->with($this->getCustomerViewTemplate(), ['entity' => $customer])
            ->willReturn(self::RENDER_HTML);

        $this->customerViewListener->onCustomerView($this->event);
    }

    public function testOnCustomerUserViewCreatesScrollBlock()
    {
        $this->request->expects($this->once())
            ->method('get')
            ->willReturn(1);

        $customerUser = new CustomerUser();
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityReference')
            ->with(CustomerUser::class, 1)
            ->willReturn($customerUser);

        $this->env->expects($this->once())
            ->method('render')
            ->with($this->getCustomerUserViewTemplate(), ['entity' => $customerUser])
            ->willReturn(self::RENDER_HTML);

        $this->customerViewListener->onCustomerUserView($this->event);
    }

    abstract protected function createListenerToTest(): AbstractCustomerViewListener;

    abstract protected function getCustomerViewTemplate(): string;

    abstract protected function getCustomerLabel(): string;

    abstract protected function getCustomerUserViewTemplate(): string;

    abstract protected function getCustomerUserLabel(): string;
}
