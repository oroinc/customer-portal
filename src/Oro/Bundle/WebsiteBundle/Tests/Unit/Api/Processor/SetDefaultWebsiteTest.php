<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\WebsiteBundle\Api\Processor\SetDefaultWebsite;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Tests\Unit\Entity\Stub\WebsiteAwareStub;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SetDefaultWebsiteTest extends CustomizeFormDataProcessorTestCase
{
    private const WEBSITE_FIELD_NAME = 'website';

    /** @var \PHPUnit\Framework\MockObject\MockObject|WebsiteManager */
    private $websiteManager;

    /** @var SetDefaultWebsite */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->processor = new SetDefaultWebsite(
            PropertyAccess::createPropertyAccessor(),
            $this->websiteManager,
            self::WEBSITE_FIELD_NAME
        );
    }

    private function getFormBuilder(): FormBuilderInterface
    {
        return $this->createFormBuilder()->create(
            '',
            FormType::class,
            ['data_class' => WebsiteAwareStub::class]
        );
    }

    public function testProcessWhenFormHasSubmittedWebsiteField()
    {
        $entity = new WebsiteAwareStub();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::WEBSITE_FIELD_NAME, FormType::class, ['data_class' => Website::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);
        $form->submit([self::WEBSITE_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->websiteManager->expects(self::never())
            ->method('getDefaultWebsite');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertInstanceOf(Website::class, $entity->getWebsite());
    }

    public function testProcessWhenFormHasSubmittedWebsiteFieldButItIsNotMapped()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            self::WEBSITE_FIELD_NAME,
            FormType::class,
            ['data_class' => Website::class, 'mapped' => false]
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);
        $form->submit([self::WEBSITE_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->websiteManager->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());
    }

    public function testProcessWhenFormDoesNotHaveWebsiteField()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();

        $formBuilder = $this->getFormBuilder();
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->websiteManager->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());
    }

    public function testProcessWhenFormHasNotSubmittedWebsiteField()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::WEBSITE_FIELD_NAME, FormType::class, ['data_class' => Website::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->websiteManager->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());
    }

    public function testProcessWhenFormHasNotSubmittedRenamedWebsiteField()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            'renamedWebsite',
            FormType::class,
            ['data_class' => Website::class, 'property_path' => self::WEBSITE_FIELD_NAME]
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->websiteManager->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());
    }

    public function testProcessWhenFormHasNotSubmittedWebsiteFieldAndNoWebsiteInWebsiteManager()
    {
        $entity = new WebsiteAwareStub();

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::WEBSITE_FIELD_NAME, FormType::class, ['data_class' => Website::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->websiteManager->expects(self::once())
            ->method('getDefaultWebsite')
            ->willReturn(null);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getWebsite());
    }

    public function testProcessWhenFormHasNotSubmittedWebsiteFieldButWebsiteAlreadySetToEntity()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();
        $entity->setWebsite($website);

        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(self::WEBSITE_FIELD_NAME, FormType::class, ['data_class' => Website::class]);
        $form = $formBuilder->getForm();
        $form->setData($entity);

        $this->websiteManager->expects(self::never())
            ->method('getDefaultWebsite');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());
    }
}
