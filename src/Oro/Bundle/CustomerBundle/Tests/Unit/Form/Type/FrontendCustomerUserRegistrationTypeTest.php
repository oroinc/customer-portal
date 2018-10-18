<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserRegistrationTypeTest extends FormIntegrationTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ConfigManager
     */
    protected $configManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UserManager
     */
    protected $userManager;

    /**
     * @var FrontendCustomerUserRegistrationType
     */
    protected $formType;

    /**
     * @var Customer[]
     */
    protected static $customers = [];

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userManager = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\UserManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formType = new FrontendCustomerUserRegistrationType($this->configManager, $this->userManager);
        $this->formType->setDataClass('Oro\Bundle\CustomerBundle\Entity\CustomerUser');
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->configManager, $this->userManager, $this->formType);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension([$this->formType], []),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider submitProvider
     *
     * @param CustomerUser $defaultData
     * @param array $submittedData
     * @param bool $companyNameEnabled
     * @param CustomerUser $expectedData
     * @param User $owner
     * @param boolean $isValid
     * @param array $options
     */
    public function testSubmit(
        $defaultData,
        array $submittedData,
        $companyNameEnabled,
        $expectedData,
        User $owner,
        $isValid,
        array $options = []
    ) {
        $this->configManager->expects($this->any())
            ->method('get')
            ->withConsecutive(['oro_customer.company_name_field_enabled'], ['oro_customer.default_customer_owner'])
            ->willReturnOnConsecutiveCalls($companyNameEnabled, 42);

        $repository = $this->assertUserRepositoryCall();
        $repository->expects($this->any())
            ->method('find')
            ->with(42)
            ->willReturn($owner);

        $form = $this->factory->create(FrontendCustomerUserRegistrationType::class, clone $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
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

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ObjectRepository
     */
    protected function assertUserRepositoryCall()
    {
        $repository = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->userManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        return $repository;
    }

    /**
     * @param CustomerUser $existingCustomerUser
     * @param string $property
     * @param mixed $value
     */
    protected function setPropertyValue(CustomerUser $existingCustomerUser, $property, $value)
    {
        $class = new \ReflectionClass($existingCustomerUser);
        $prop = $class->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($existingCustomerUser, $value);
    }

    /**
     * @param User $owner
     * @return CustomerUser
     */
    protected function createCustomerUserWithDefaultData(User $owner)
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
