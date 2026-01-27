<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserProfileType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Oro\Bundle\UserBundle\Tests\Unit\Stub\ChangePasswordTypeStub;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserProfileTypeTest extends FormIntegrationTestCase
{
    private Customer $customer;
    private FrontendCustomerUserProfileType $formType;

    protected function setUp(): void
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('get')
            ->with('oro_customer.company_name_field_enabled')
            ->willReturn(true);
        $featureChecker = $this->createMock(FeatureChecker::class);

        $this->formType = new FrontendCustomerUserProfileType($configManager);
        $this->formType->setFeatureChecker($featureChecker);
        $this->formType->setDataClass(CustomerUser::class);

        $this->customer = new Customer();
        ReflectionUtil::setId($this->customer, 1);

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub([
                        $this->customer->getId() => $this->customer
                    ]),
                    ChangePasswordType::class => new ChangePasswordTypeStub()
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertEquals('oro_customer_frontend_customer_user_profile', $this->formType->getBlockPrefix());
    }

    public function testSubmitForNewUser(): void
    {
        $entity = new CustomerUser();
        $entity->setCustomer($this->customer);

        $form = $this->factory->create(FrontendCustomerUserProfileType::class, $entity);
        $this->assertSame($entity, $form->getData());

        $form->submit([]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($entity, $form->getData());
    }

    public function testSubmitForExistingUser(): void
    {
        $entity = new CustomerUser();
        ReflectionUtil::setId($entity, 42);
        $entity->setFirstName('John');
        $entity->setLastName('Doe');
        $entity->setEmail('johndoe@example.com');
        $entity->setPassword('123456');
        $entity->setCustomer($this->customer);

        $expectedEntity = clone $entity;
        $expectedEntity->setFirstName('John UP');
        $expectedEntity->setLastName('Doe UP');
        $expectedEntity->setEmail('johndoe_up@example.com');

        $form = $this->factory->create(FrontendCustomerUserProfileType::class, $entity);
        $this->assertSame($entity, $form->getData());

        $form->submit([
            'firstName' => $expectedEntity->getFirstName(),
            'lastName' => $expectedEntity->getLastName(),
            'email' => $expectedEntity->getEmail(),
            'customer' => $this->customer->getId(),
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedEntity, $form->getData());
    }
}
