<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserProfileType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Oro\Bundle\UserBundle\Tests\Unit\Stub\ChangePasswordTypeStub;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserProfileTypeTest extends FormIntegrationTestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var FrontendCustomerUserProfileType */
    private $formType;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->formType = new FrontendCustomerUserProfileType($this->configManager);
        $this->formType->setDataClass(CustomerUser::class);
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
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub(),
                    ChangePasswordType::class => new ChangePasswordTypeStub()
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider submitProvider
     */
    public function testSubmit(CustomerUser $defaultData, array $submittedData, CustomerUser $expectedData)
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.company_name_field_enabled')
            ->willReturn(true);
        $form = $this->factory->create(FrontendCustomerUserProfileType::class, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $entity = new CustomerUser();
        $customer = new Customer();
        $entity->setCustomer($customer);
        $existingEntity = new CustomerUser();
        ReflectionUtil::setId($existingEntity, 42);
        $existingEntity->setFirstName('John');
        $existingEntity->setLastName('Doe');
        $existingEntity->setEmail('johndoe@example.com');
        $existingEntity->setPassword('123456');
        $existingEntity->setCustomer($customer);

        $updatedEntity = clone $existingEntity;
        $updatedEntity->setFirstName('John UP');
        $updatedEntity->setLastName('Doe UP');
        $updatedEntity->setEmail('johndoe_up@example.com');

        return [
            'new user' => [
                'defaultData' => $entity,
                'submittedData' => [],
                'expectedData' => $entity
            ],
            'updated user' => [
                'defaultData' => $existingEntity,
                'submittedData' => [
                    'firstName' => $updatedEntity->getFirstName(),
                    'lastName' => $updatedEntity->getLastName(),
                    'email' => $updatedEntity->getEmail(),
                    'customer' => $updatedEntity->getCustomer()->getName(),
                ],
                'expectedData' => $updatedEntity
            ]
        ];
    }
}
