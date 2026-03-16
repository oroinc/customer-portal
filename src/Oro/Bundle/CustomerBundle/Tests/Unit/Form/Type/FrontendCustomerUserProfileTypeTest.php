<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserProfileType;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserProfileTypeTest extends FormIntegrationTestCase
{
    private FrontendCustomerUserProfileType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('get')
            ->with('oro_customer.company_name_field_enabled')
            ->willReturn(true);
        $featureChecker = $this->createMock(FeatureChecker::class);

        $this->formType = new FrontendCustomerUserProfileType($configManager, $featureChecker);
        $this->formType->setDataClass(CustomerUser::class);

        parent::setUp();
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
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

        $expectedEntity = clone $entity;
        $expectedEntity->setFirstName('John UP');
        $expectedEntity->setLastName('Doe UP');

        $form = $this->factory->create(FrontendCustomerUserProfileType::class, $entity);
        $this->assertSame($entity, $form->getData());

        $form->submit([
            'firstName' => $expectedEntity->getFirstName(),
            'lastName' => $expectedEntity->getLastName(),
        ]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedEntity, $form->getData());
    }
}
