<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\WebsiteBundle\Api\Processor\SetWebsite;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Tests\Unit\Entity\Stub\WebsiteAwareStub;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SetWebsiteTest extends TypeTestCase
{
    private const WEBSITE_FIELD_NAME = 'website';

    /** @var \PHPUnit\Framework\MockObject\MockObject|WebsiteManager */
    private $websiteManager;

    /** @var SetWebsite */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->processor = new SetWebsite(
            PropertyAccess::createPropertyAccessor(),
            $this->websiteManager,
            self::WEBSITE_FIELD_NAME
        );
    }

    /**
     * @return FormBuilderInterface
     */
    private function getFormBuilder()
    {
        return $this->builder->create(
            null,
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
            ->method('getCurrentWebsite');

        $context = new CustomizeFormDataContext();
        $context->setForm($form);
        $context->setData($entity);
        $this->processor->process($context);

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
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $context = new CustomizeFormDataContext();
        $context->setForm($form);
        $context->setData($entity);
        $this->processor->process($context);

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
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $context = new CustomizeFormDataContext();
        $context->setForm($form);
        $context->setData($entity);
        $this->processor->process($context);

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
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $context = new CustomizeFormDataContext();
        $context->setForm($form);
        $context->setData($entity);
        $this->processor->process($context);

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
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $context = new CustomizeFormDataContext();
        $context->setForm($form);
        $context->setData($entity);
        $this->processor->process($context);

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
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $context = new CustomizeFormDataContext();
        $context->setForm($form);
        $context->setData($entity);
        $this->processor->process($context);

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
            ->method('getCurrentWebsite');

        $context = new CustomizeFormDataContext();
        $context->setForm($form);
        $context->setData($entity);
        $this->processor->process($context);

        self::assertSame($website, $entity->getWebsite());
    }
}
