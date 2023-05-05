<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserPasswordResetType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CustomerUserPasswordResetTypeTest extends FormIntegrationTestCase
{
    private CustomerUserPasswordResetType $formType;

    protected function setUp(): void
    {
        $this->formType = new CustomerUserPasswordResetType();
        $this->formType->setDataClass(CustomerUser::class);
        parent::setUp();
    }

    /**
     * {@inheritDoc}
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
    public function testSubmit(CustomerUser $defaultData, array $submittedData, CustomerUser $expectedData)
    {
        $form = $this->factory->create(CustomerUserPasswordResetType::class, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitProvider(): array
    {
        $entity = new CustomerUser();
        $expectedEntity = new CustomerUser();
        $expectedEntity->setSalt($entity->getSalt());
        $expectedEntity->setPlainPassword('new password');

        return [
            'reset password' => [
                'defaultData' => $entity,
                'submittedData' => [
                    'plainPassword' =>  [
                        'first' => 'new password',
                        'second' => 'new password'
                    ]
                ],
                'expectedData' => $expectedEntity
            ]
        ];
    }
}
