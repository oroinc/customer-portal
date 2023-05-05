<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\ApiBundle\Tests\Unit\Processor\CustomizeFormData\CustomizeFormDataProcessorTestCase;
use Oro\Bundle\ApiBundle\Validator\Constraints\AccessGranted;
use Oro\Bundle\CustomerBundle\Api\Processor\SetSystemOrganization;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class SetSystemOrganizationTest extends CustomizeFormDataProcessorTestCase
{
    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var OwnershipMetadataProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $ownershipMetadataProvider;

    /** @var SetSystemOrganization */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->ownershipMetadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);

        $this->processor = new SetSystemOrganization(
            PropertyAccess::createPropertyAccessor(),
            $this->tokenAccessor,
            $this->ownershipMetadataProvider
        );
    }

    private function getFormBuilder(): FormBuilderInterface
    {
        return $this->createFormBuilder()->create(
            '',
            FormType::class,
            ['data_class' => Customer::class]
        );
    }

    private function getForm(Customer $entity, string $fieldName, array $fieldOptions = []): FormInterface
    {
        $formBuilder = $this->getFormBuilder();
        $formBuilder->add(
            $fieldName,
            FormType::class,
            array_merge(
                ['constraints' => [new AccessGranted(['groups' => ['api']])]],
                $fieldOptions
            )
        );
        $form = $formBuilder->getForm();
        $form->setData($entity);

        return $form;
    }

    public function testProcessForEntityWithoutOwnership(): void
    {
        $entity = new Customer();

        $form = $this->getForm($entity, 'organization', ['data_class' => Organization::class]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Customer::class)
            ->willReturn(new OwnershipMetadata('NONE'));

        $this->tokenAccessor->expects(self::never())
            ->method('getOrganization');

        $this->context->setClassName(Customer::class);
        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get('organization')->getConfig()->getOption('constraints')
        );
    }

    public function testProcessForEntityWithOrganizationOwnershipAndOrganizationAlreadySet(): void
    {
        $entity = new Customer();
        $organization = new Organization();
        $entity->setOrganization($organization);

        $form = $this->getForm($entity, 'organization', ['data_class' => Organization::class]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Customer::class)
            ->willReturn(new OwnershipMetadata(
                'ORGANIZATION',
                'organization',
                'organization_id'
            ));

        $this->tokenAccessor->expects(self::never())
            ->method('getOrganization');

        $this->context->setClassName(Customer::class);
        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($organization, $entity->getOrganization());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get('organization')->getConfig()->getOption('constraints')
        );
    }

    public function testProcessForEntityWithOrganizationOwnershipAndNoCurrentOrganization(): void
    {
        $entity = new Customer();

        $form = $this->getForm($entity, 'organization', ['data_class' => Organization::class]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Customer::class)
            ->willReturn(new OwnershipMetadata(
                'ORGANIZATION',
                'organization',
                'organization_id'
            ));

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn(null);

        $this->context->setClassName(Customer::class);
        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getOrganization());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get('organization')->getConfig()->getOption('constraints')
        );
    }

    public function testProcessForEntityWithOrganizationOwnershipAndNoOrganization(): void
    {
        $entity = new Customer();
        $organization = new Organization();

        $form = $this->getForm($entity, 'organization', ['data_class' => Organization::class]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Customer::class)
            ->willReturn(new OwnershipMetadata(
                'ORGANIZATION',
                'organization',
                'organization_id'
            ));

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->setClassName(Customer::class);
        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($organization, $entity->getOrganization());

        self::assertEquals(
            [],
            $form->get('organization')->getConfig()->getOption('constraints')
        );
    }

    /**
     * @dataProvider userAndBusinessUnitOwnershipDataProvider
     */
    public function testProcessForEntityWithNotOrganizationOwnershipAndOrganizationAlreadySet(string $ownerType): void
    {
        $entity = new Customer();
        $organization = new Organization();
        $entity->setOrganization($organization);

        $form = $this->getForm($entity, 'organization', ['data_class' => Organization::class]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Customer::class)
            ->willReturn(new OwnershipMetadata(
                $ownerType,
                'owner',
                'owner_id',
                'organization',
                'organization_id'
            ));

        $this->tokenAccessor->expects(self::never())
            ->method('getOrganization');

        $this->context->setClassName(Customer::class);
        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($organization, $entity->getOrganization());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get('organization')->getConfig()->getOption('constraints')
        );
    }

    /**
     * @dataProvider userAndBusinessUnitOwnershipDataProvider
     */
    public function testProcessForEntityWithNotOrganizationOwnershipAndNoCurrentOrganization(string $ownerType): void
    {
        $entity = new Customer();

        $form = $this->getForm($entity, 'organization', ['data_class' => Organization::class]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Customer::class)
            ->willReturn(new OwnershipMetadata(
                $ownerType,
                'owner',
                'owner_id',
                'organization',
                'organization_id'
            ));

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn(null);

        $this->context->setClassName(Customer::class);
        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertNull($entity->getOrganization());

        self::assertEquals(
            [new AccessGranted(['groups' => ['api']])],
            $form->get('organization')->getConfig()->getOption('constraints')
        );
    }

    /**
     * @dataProvider userAndBusinessUnitOwnershipDataProvider
     */
    public function testProcessForEntityWithNotOrganizationOwnershipAndNoOrganization(string $ownerType): void
    {
        $entity = new Customer();
        $organization = new Organization();

        $form = $this->getForm($entity, 'organization', ['data_class' => Organization::class]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Customer::class)
            ->willReturn(new OwnershipMetadata(
                $ownerType,
                'owner',
                'owner_id',
                'organization',
                'organization_id'
            ));

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);

        $this->context->setClassName(Customer::class);
        $this->context->setForm($form);
        $this->context->setData($entity);
        $this->processor->process($this->context);

        self::assertSame($organization, $entity->getOrganization());

        self::assertEquals(
            [],
            $form->get('organization')->getConfig()->getOption('constraints')
        );
    }

    public function userAndBusinessUnitOwnershipDataProvider(): array
    {
        return [
            ['USER'],
            ['BUSINESS_UNIT']
        ];
    }
}
