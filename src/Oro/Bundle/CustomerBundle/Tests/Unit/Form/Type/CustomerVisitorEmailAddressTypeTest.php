<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerVisitorEmailAddressType;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\EmailBundle\Form\Type\EmailAddressType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validation;

class CustomerVisitorEmailAddressTypeTest extends FormIntegrationTestCase
{
    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        parent::setUp();
    }

    public function testCreateByCustomerVisitor()
    {
        /** @var AnonymousCustomerUserToken|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(AnonymousCustomerUserToken::class);

        $this->tokenStorage->expects($this->exactly(2))
            ->method('getToken')
            ->will($this->returnValue($token));

        $form = $this->factory->create(CustomerVisitorEmailAddressType::class);
        $this->assertInstanceOf(EmailAddressType::class, $form->getConfig()->getType()->getParent()->getInnerType());
        $this->assertTrue($form->getConfig()->getRequired());
    }

    /**
     * @dataProvider getNotValidEmail
     *
     * @param string $submittedData
     * @param string $expectedError
     */
    public function testSubmitNotValidEmailByCustomerVisitor($submittedData, $expectedError)
    {
        /** @var AnonymousCustomerUserToken|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(AnonymousCustomerUserToken::class);

        $this->tokenStorage->expects($this->exactly(2))
            ->method('getToken')
            ->will($this->returnValue($token));

        $form = $this->factory->create(CustomerVisitorEmailAddressType::class);
        $form->submit($submittedData);
        $this->assertFalse($form->isValid());
        $this->assertContains($expectedError, (string)$form->getErrors(true, false));
    }

    /**
     * @return array
     */
    public function getNotValidEmail()
    {
        return [
            'empty string' => [
                'submittedData' => '',
                'expectedError' => 'This value should not be blank'
            ],
            'not email string' => [
                'submittedData' => 'email',
                'expectedError' => 'This value is not a valid email address'
            ],
        ];
    }

    public function testCreateByCustomerUser()
    {
        /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->createMock(TokenInterface::class);

        $this->tokenStorage->expects($this->exactly(2))
            ->method('getToken')
            ->will($this->returnValue($token));

        $form = $this->factory->create(CustomerVisitorEmailAddressType::class);
        $this->assertInstanceOf(HiddenType::class, $form->getConfig()->getType()->getParent()->getInnerType());
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $type = new CustomerVisitorEmailAddressType($this->tokenStorage);

        return [
            new PreloadedExtension([$type], []),
            new ValidatorExtension(Validation::createValidator())
        ];
    }
}
