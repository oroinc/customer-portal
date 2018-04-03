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
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendCustomerUserHandlerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /** @var CustomerUserManager|\PHPUnit_Framework_MockObject_MockObject */
    private $userManager;

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $doctrineHelper;

    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $eventDispatcher;

    /** @var Request|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $form;

    /** @var FrontendCustomerUserHandler */
    private $handler;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->userManager = $this->createMock(CustomerUserManager::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->request = new Request();
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())
            ->method('getMasterRequest')
            ->willReturn($this->request);

        $this->form = $this->createMock(FormInterface::class);
        $this->handler = new FrontendCustomerUserHandler(
            $this->eventDispatcher,
            $this->doctrineHelper,
            $requestStack,
            $this->userManager
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Data should be instance of %s, but %s is given
     */
    public function testProcessInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Data should be instance of %s, but %s is given',
            CustomerUser::class,
            \stdClass::class
        ));

        $this->handler->process(new \stdClass(), $this->form, $this->request);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Test flush exception
     */
    public function testProcessFlushException()
    {
        $entity = new CustomerUser();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->will($this->returnValue($em));
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
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->will($this->returnValue($em));
        $em->expects($this->once())
            ->method('beginTransaction');
        $em->expects($this->once())
            ->method('commit');

        $website = new Website();
        $website->setName('Current Website');
        $this->request->attributes->set('current_website', $website);

        $this->userManager->expects($this->once())
            ->method('register')
            ->with($entity);
        $this->userManager->expects($this->once())
            ->method('updateUser')
            ->with($entity);

        $this->assertProcessAfterEventsTriggered($this->form, $entity);

        $this->assertTrue($this->handler->process($entity, $this->form, $this->request));
        $this->assertEquals('Current Website', $website);
    }

    public function testProcessExistingCustomerUser()
    {
        $entity = $this->getEntity(CustomerUser::class, ['id' => 1]);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->request->setMethod('POST');

        $em = $this->createMock(EntityManager::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->will($this->returnValue($em));
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

    /**
     * @param FormInterface $form
     * @param CustomerUser $entity
     */
    private function assertProcessAfterEventsTriggered(FormInterface $form, $entity)
    {
        $this->eventDispatcher->expects($this->at(0))
            ->method('dispatch')
            ->withConsecutive(
                [Events::BEFORE_FORM_DATA_SET, new FormProcessEvent($form, $entity)],
                [Events::BEFORE_FORM_SUBMIT, new FormProcessEvent($form, $entity)],
                [Events::BEFORE_FLUSH, new AfterFormProcessEvent($form, $entity)],
                [Events::AFTER_FLUSH, new AfterFormProcessEvent($form, $entity)]
            );
    }
}
