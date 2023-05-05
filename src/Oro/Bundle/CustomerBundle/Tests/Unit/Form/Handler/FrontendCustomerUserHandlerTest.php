<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
use Oro\Bundle\CustomerBundle\Tests\Unit\Entity\Stub\CustomerUserStub;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\FormBundle\Event\FormHandler\FormProcessEvent;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\RequestWebsiteProvider;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FrontendCustomerUserHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserManager|\PHPUnit\Framework\MockObject\MockObject */
    private $userManager;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $eventDispatcher;

    /** @var RequestWebsiteProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $requestWebsiteProvider;

    /** @var Request|\PHPUnit\Framework\MockObject\MockObject */
    private $request;

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var FrontendCustomerUserHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->requestWebsiteProvider = $this->createMock(RequestWebsiteProvider::class);
        $this->request = new Request();

        $this->form = $this->createMock(FormInterface::class);
        $this->handler = new FrontendCustomerUserHandler(
            $this->eventDispatcher,
            $this->doctrineHelper,
            $this->requestWebsiteProvider,
            $this->userManager
        );
    }

    public function testProcessInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data should be instance of %s, but %s is given');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data should be instance of %s, but %s is given',
            CustomerUser::class,
            \stdClass::class
        ));

        $this->handler->process(new \stdClass(), $this->form, $this->request);
    }

    public function testProcessFlushException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test flush exception');

        $entity = new CustomerUserStub();

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('rollback');

        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity)
            ->willThrowException(new \Exception('Test flush exception'));

        $this->handler->process($entity, $this->form, $this->request);
    }

    public function testProcessUnexpectedRequestMethod(): void
    {
        $entity = new CustomerUserStub();

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::never())
            ->method('submit');
        $this->form->expects(self::never())
            ->method('isValid');

        $this->request->setMethod('GET');

        self::assertFalse($this->handler->process($entity, $this->form, $this->request));
    }

    public function testProcessNewCustomerUser(): void
    {
        $entity = new CustomerUserStub();

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('commit');

        $website = new Website();
        $this->requestWebsiteProvider->expects(self::once())
            ->method('getWebsite')
            ->willReturn($website);

        $this->userManager->expects(self::once())
            ->method('register')
            ->with($entity);
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        self::assertTrue($this->handler->process($entity, $this->form, $this->request));
        self::assertSame($website, $entity->getWebsite());
    }

    public function testProcessNewCustomerUserAndNoCurrentWebsite(): void
    {
        $entity = new CustomerUserStub();

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('commit');

        $this->requestWebsiteProvider->expects(self::once())
            ->method('getWebsite')
            ->willReturn(null);

        $this->userManager->expects(self::once())
            ->method('register')
            ->with($entity);
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        self::assertTrue($this->handler->process($entity, $this->form, $this->request));
        self::assertNull($entity->getWebsite());
    }

    public function testProcessExistingCustomerUser(): void
    {
        $entity = new CustomerUserStub();
        ReflectionUtil::setId($entity, 1);

        $this->form->expects(self::once())
            ->method('setData')
            ->with($entity);
        $this->form->expects(self::once())
            ->method('submit')
            ->with([], true);
        $this->form->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('beginTransaction');
        $em->expects(self::once())
            ->method('commit');

        $this->userManager->expects(self::never())
            ->method('register');
        $this->userManager->expects(self::once())
            ->method('updateUser')
            ->with($entity);
        $this->userManager->expects(self::once())
            ->method('reloadUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        self::assertTrue($this->handler->process($entity, $this->form, $this->request));
    }

    private function assertProcessAfterEventsTriggered(FormInterface $form, CustomerUser $entity): void
    {
        $this->eventDispatcher->expects(self::exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [new FormProcessEvent($form, $entity), Events::BEFORE_FORM_DATA_SET],
                [new FormProcessEvent($form, $entity), Events::BEFORE_FORM_SUBMIT],
                [new AfterFormProcessEvent($form, $entity), Events::BEFORE_FLUSH],
                [new AfterFormProcessEvent($form, $entity), Events::AFTER_FLUSH]
            );
    }
}
