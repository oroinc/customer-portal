<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserRegistrationTypeTest extends FormIntegrationTestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var FrontendCustomerUserRegistrationType */
    private $formType;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);

        $this->formType = new FrontendCustomerUserRegistrationType($this->configManager, $this->doctrine);
        $this->formType->setDataClass(CustomerUser::class);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([$this->formType], []),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider submitProvider
     */
    public function testSubmit(
        CustomerUser $defaultData,
        array $submittedData,
        bool $companyNameEnabled,
        CustomerUser $expectedData,
        User $owner,
        bool $isValid,
        array $options = []
    ) {
        $this->configManager->expects($this->any())
            ->method('get')
            ->withConsecutive(['oro_customer.company_name_field_enabled'], ['oro_customer.default_customer_owner'])
            ->willReturnOnConsecutiveCalls($companyNameEnabled, 42);

        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($em);
        $em->expects($this->any())
            ->method('find')
            ->with(User::class, 42)
            ->willReturn($owner);

        $form = $this->factory->create(FrontendCustomerUserRegistrationType::class, clone $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $owner = new User();

        $userWithoutCompanyName = new CustomerUser();
        $expectedUserWithoutCompanyName = $this->createCustomerUserWithDefaultData($owner);
        $userWithoutCompanyName->setSalt($expectedUserWithoutCompanyName->getSalt());

        $userWithCompanyName = new CustomerUser();
        $expectedUserWithCompanyName = $this->createCustomerUserWithDefaultData($owner);
        $expectedUserWithCompanyName->getCustomer()->setName('Test Company');
        $userWithCompanyName->setSalt($expectedUserWithCompanyName->getSalt());

        $userWithLongCompanyName = new CustomerUser();
        $expectedUserWithLongCompanyName = $this->createCustomerUserWithDefaultData($owner);
        // @codingStandardsIgnoreStart
        $expectedUserWithLongCompanyName->getCustomer()->setName('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.');
        // @codingStandardsIgnoreEnd
        $userWithLongCompanyName->setSalt($expectedUserWithLongCompanyName->getSalt());

        $userWithCompanyNameDisabled = new CustomerUser();
        $expectedUserWithCompanyNameDisabled = $this->createCustomerUserWithDefaultData($owner);
        $userWithCompanyNameDisabled->setSalt($expectedUserWithCompanyNameDisabled->getSalt());

        return [
            'new user without company name' => [
                'defaultData' => $userWithoutCompanyName,
                'submittedData' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'johndoe@example.com',
                    'plainPassword' => [
                        'first' => '123456',
                        'second' => '123456'
                    ]
                ],
                'companyNameEnabled' => true,
                'expectedData' => $expectedUserWithoutCompanyName,
                'owner' => $owner,
                'isValid' => false
            ],
            'new user with company name' => [
                'defaultData' => $userWithCompanyName,
                'submittedData' => [
                    'companyName' => 'Test Company',
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'johndoe@example.com',
                    'plainPassword' => [
                        'first' => '123456',
                        'second' => '123456'
                    ]
                ],
                'companyNameEnabled' => true,
                'expectedData' => $expectedUserWithCompanyName,
                'owner' => $owner,
                'isValid' => true
            ],
            'new user with long company name' => [
                'defaultData' => $userWithLongCompanyName,
                'submittedData' => [
                    // @codingStandardsIgnoreStart
                    'companyName' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                    // @codingStandardsIgnoreEnd
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'johndoe@example.com',
                    'plainPassword' => [
                        'first' => '123456',
                        'second' => '123456'
                    ]
                ],
                'companyNameEnabled' => true,
                'expectedData' => $expectedUserWithLongCompanyName,
                'owner' => $owner,
                'isValid' => false
            ],
            'new user with company name disabled' => [
                'defaultData' => $userWithCompanyNameDisabled,
                'submittedData' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'johndoe@example.com',
                    'plainPassword' => [
                        'first' => '123456',
                        'second' => '123456'
                    ]
                ],
                'companyNameEnabled' => false,
                'expectedData' => $expectedUserWithCompanyNameDisabled,
                'owner' => $owner,
                'isValid' => true
            ],
        ];
    }

    private function createCustomerUserWithDefaultData(User $owner): CustomerUser
    {
        $customerUser = new CustomerUser();

        return $customerUser
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('johndoe@example.com')
            ->setOwner($owner)
            ->setPlainPassword('123456')
            ->createCustomer();
    }
}
