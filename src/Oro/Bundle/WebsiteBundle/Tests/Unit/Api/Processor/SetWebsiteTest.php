<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\ApiBundle\Validator\Constraints\AccessGranted;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\WebsiteBundle\Api\Processor\SetWebsite;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Tests\Unit\Entity\Stub\WebsiteAwareStub;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class SetWebsiteTest extends CustomizeFormDataProcessorTestCase
{
    private const WEBSITE_FIELD_NAME = 'website';

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
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

    private function getFormBuilder(): FormBuilderInterface
    {
        return $this->createFormBuilder()->create(
            '',
            FormType::class,
            ['data_class' => WebsiteAwareStub::class]
        );
    }

    private function getForm(
        WebsiteAwareStub $entity,
        array $websiteFieldOptions = [],
        string $websiteFieldName = self::WEBSITE_FIELD_NAME
    ): FormInterface {
        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            $websiteFieldName,
            FormType::class,
            array_merge(
                ['data_class' => Website::class, 'constraints' => [new AccessGranted(['groups' => ['api']])]],
                $websiteFieldOptions
            )
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);

        return $form;
    }

    public function testProcessWhenFormHasSubmittedWebsiteField()
    {
        $entity = new WebsiteAwareStub();

        $form = $this->getForm($entity);
        $form->submit([self::WEBSITE_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->websiteManager->expects(self::never())
            ->method('getCurrentWebsite');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertInstanceOf(Website::class, $entity->getWebsite());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::WEBSITE_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasSubmittedWebsiteFieldButItIsNotMapped()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();

        $form = $this->getForm($entity, ['mapped' => false]);
        $form->submit([self::WEBSITE_FIELD_NAME => []], false);
        self::assertTrue($form->isSynchronized());

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());

        self::assertEquals(
            [],
            $form->get(self::WEBSITE_FIELD_NAME)->getConfig()->getOption('constraints')
        );
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

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());
    }

    public function testProcessWhenFormHasNotSubmittedWebsiteField()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();

        $form = $this->getForm($entity);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());

        self::assertEquals(
            [],
            $form->get(self::WEBSITE_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedRenamedWebsiteField()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();

        $form = $this->getForm($entity, ['property_path' => self::WEBSITE_FIELD_NAME], 'renamedWebsite');

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());

        self::assertEquals(
            [],
            $form->get('renamedWebsite')->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedWebsiteFieldAndNoWebsiteInWebsiteManager()
    {
        $entity = new WebsiteAwareStub();

        $form = $this->getForm($entity);

        $this->websiteManager->expects(self::once())
            ->method('getCurrentWebsite')
            ->willReturn(null);

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getWebsite());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::WEBSITE_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }

    public function testProcessWhenFormHasNotSubmittedWebsiteFieldButWebsiteAlreadySetToEntity()
    {
        $entity = new WebsiteAwareStub();
        $website = new Website();
        $entity->setWebsite($website);

        $form = $this->getForm($entity);

        $this->websiteManager->expects(self::never())
            ->method('getCurrentWebsite');

        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($website, $entity->getWebsite());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get(self::WEBSITE_FIELD_NAME)->getConfig()->getOption('constraints')
        );
    }
}
