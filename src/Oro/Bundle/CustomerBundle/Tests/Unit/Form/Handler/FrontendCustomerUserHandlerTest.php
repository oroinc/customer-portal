<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Handler\FrontendCustomerUserHandler;
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

    public function testProcessInvalidArgumentException()
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

    public function testProcessFlushException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test flush exception');

        $entity = new CustomerUser();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with([], true);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects($this->once())
            ->method('beginTransaction');
        $em->expects($this->once())
            ->method('rollback');

        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($entity)
            ->willThrowException(new \Exception('Test flush exception'));

        $this->handler->process($entity, $this->form, $this->request);
    }

    public function testProcessUnexpectedRequestMethod()
    {
        $entity = new CustomerUser();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->form->expects($this->never())
            ->method('submit');
        $this->form->expects($this->never())
            ->method('isValid');

        $this->request->setMethod('GET');

        $this->assertFalse($this->handler->process($entity, $this->form, $this->request));
    }

    public function testProcessNewCustomerUser()
    {
        $entity = new CustomerUser();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with([], true);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects($this->once())
            ->method('beginTransaction');
        $em->expects($this->once())
            ->method('commit');

        $website = new Website();
        $this->requestWebsiteProvider->expects($this->once())
            ->method('getWebsite')
            ->willReturn($website);

        $this->userManager->expects($this->once())
            ->method('register')
            ->with($entity);
        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        $this->assertTrue($this->handler->process($entity, $this->form, $this->request));
        $this->assertSame($website, $entity->getWebsite());
    }

    public function testProcessNewCustomerUserAndNoCurrentWebsite()
    {
        $entity = new CustomerUser();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with([], true);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects($this->once())
            ->method('beginTransaction');
        $em->expects($this->once())
            ->method('commit');

        $this->requestWebsiteProvider->expects($this->once())
            ->method('getWebsite')
            ->willReturn(null);

        $this->userManager->expects($this->once())
            ->method('register')
            ->with($entity);
        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        $this->assertTrue($this->handler->process($entity, $this->form, $this->request));
        $this->assertNull($entity->getWebsite());
    }

    public function testProcessExistingCustomerUser()
    {
        $entity = new CustomerUser();
        ReflectionUtil::setId($entity, 1);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with([], true);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->willReturn($em);
        $em->expects($this->once())
            ->method('beginTransaction');
        $em->expects($this->once())
            ->method('commit');

        $this->userManager->expects($this->never())
            ->method('register');
        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($entity);
        $this->userManager->expects($this->once())
            ->method('reloadUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        $this->assertTrue($this->handler->process($entity, $this->form, $this->request));
    }

    private function assertProcessAfterEventsTriggered(FormInterface $form, CustomerUser $entity): void
    {
        $this->eventDispatcher->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [new FormProcessEvent($form, $entity), Events::BEFORE_FORM_DATA_SET],
                [new FormProcessEvent($form, $entity), Events::BEFORE_FORM_SUBMIT],
                [new AfterFormProcessEvent($form, $entity), Events::BEFORE_FLUSH],
                [new AfterFormProcessEvent($form, $entity), Events::AFTER_FLUSH]
            );
    }
}
