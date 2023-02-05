<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleSelectType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserType;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendOwnerSelectType;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\AddressCollectionTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\EntitySelectTypeStub;
use Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type\Stub\FrontendOwnerSelectTypeStub;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validation;

class FrontendCustomerUserTypeTest extends CustomerUserTypeTest
{
    /** @var FrontendCustomerUserType */
    protected $formType;

    /** @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteManager;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->formType = new FrontendCustomerUserType(
            $this->authorizationChecker,
            $this->tokenAccessor,
            $this->websiteManager
        );
        $this->formType->setCustomerUserClass(CustomerUser::class);

        FormIntegrationTestCase::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        $user = new CustomerUser();
        $user->setCustomer($this->getCustomer(1));
        $this->tokenAccessor->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $customerUserType = new CustomerUserType($this->authorizationChecker, $this->tokenAccessor);
        $customerUserType->setDataClass(CustomerUser::class);
        $customerUserType->setAddressClass(CustomerUserAddress::class);

        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    $customerUserType,
                    FrontendCustomerUserRoleSelectType::class => new EntitySelectTypeStub(
                        $this->getRoles(),
                        new CustomerUserRoleSelectType($this->createTranslator())
                    ),
                    CustomerSelectType::class => new EntityTypeStub($this->getCustomers()),
                    FrontendOwnerSelectType::class => new FrontendOwnerSelectTypeStub(),
                    AddressCollectionType::class => new AddressCollectionTypeStub(),
                    new EntityTypeStub($this->getAddresses()),
                ],
                []
            ),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    /**
     * @dataProvider submitProvider
     */
    public function testSubmit(
        CustomerUser $defaultData,
        array $submittedData,
        CustomerUser $expectedData,
        bool $rolesGranted = true
    ) {
        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturnCallback(function ($acl) {
                return $acl !== 'oro_customer_customer_user_role_view';
            });

        $form = $this->factory->create(FrontendCustomerUserType::class, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $newCustomerUser = new CustomerUser();
        $customer = new Customer();
        $newCustomerUser->setCustomer($customer);
        $existingCustomerUser = new CustomerUser();
        ReflectionUtil::setId($existingCustomerUser, 42);
        $existingCustomerUser->setFirstName('John');
        $existingCustomerUser->setLastName('Doe');
        $existingCustomerUser->setEmail('johndoe@example.com');
        $existingCustomerUser->setPassword('123456');
        $existingCustomerUser->setCustomer($customer);
        $existingCustomerUser->addAddress($this->getAddresses()[1]);

        $alteredExistingCustomerUser = clone $existingCustomerUser;
        $alteredExistingCustomerUser->setEnabled(false);
        $alteredExistingCustomerUser->setCustomer($customer);

        $alteredExistingCustomerUserWithRole = clone $alteredExistingCustomerUser;
        $alteredExistingCustomerUserWithRole->setUserRoles([$this->getRole(2, 'test02')]);

        $alteredExistingCustomerUserWithAddresses = clone $alteredExistingCustomerUser;
        $alteredExistingCustomerUserWithAddresses->addAddress($this->getAddresses()[2]);

        return
            [
                'user without submitted data' => [
                    'defaultData' => $newCustomerUser,
                    'submittedData' => [],
                    'expectedData' => $newCustomerUser,
                ],
                'altered existing user' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'johndoe@example.com',
                        'customer' => $existingCustomerUser->getCustomer()->getName(),
                    ],
                    'expectedData' => $alteredExistingCustomerUser,
                ],
                'altered existing user with roles' => [
                    'defaultData' => $existingCustomerUser,
                    'submittedData' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'johndoe@example.com',
                        'customer' => $existingCustomerUser->getCustomer()->getName(),
                        'userRoles' => [2],
                    ],
                    'expectedData' => $alteredExistingCustomerUserWithRole
                ],
            ];
    }

    public function testGetName()
    {
        $this->assertEquals(FrontendCustomerUserType::NAME, $this->formType->getName());
    }

    /**
     * @depends testSubmit
     */
    public function testOnPreSetData()
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $formType = new FrontendCustomerUserType(
            $authorizationChecker,
            $tokenAccessor,
            $this->websiteManager
        );

        $event = $this->createMock(FormEvent::class);

        $tokenAccessor->expects($this->any())
            ->method('getUser')
            ->willReturn(null);

        $formType->onPreSetData($event);
    }

    /**
     * @dataProvider onSubmitDataProvider
     */
    public function testOnSubmit(?int $customerUserId, Website $website = null, Website $expectedWebsite = null)
    {
        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->willReturn(false);

        $this->websiteManager->expects($this->any())
            ->method('getCurrentWebsite')
            ->willReturn($website);

        $customer = new Customer();
        $newCustomerUser = new CustomerUser();
        ReflectionUtil::setId($newCustomerUser, $customerUserId);
        $newCustomerUser->setCustomer($customer);
        $form = $this->factory->create(FrontendCustomerUserType::class, $newCustomerUser, []);

        $form->submit([]);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedWebsite, $form->getData()->getWebsite());
    }

    public function onSubmitDataProvider(): array
    {
        $website = new Website();

        return [
            'no id with website' => [null, $website, $website],
            'id with website' => [1, new Website(), null],
            'id without website' => [1, null, null],
            'no id without website' => [null, null, null],
        ];
    }
}
