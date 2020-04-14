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
    const DATA_CLASS = 'Oro\Bundle\CustomerBundle\Entity\CustomerUser';

    /** @var CustomerUserPasswordResetType */
    protected $formType;

    protected function setUp(): void
    {
        $this->formType = new CustomerUserPasswordResetType();
        $this->formType->setDataClass(self::DATA_CLASS);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->formType);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension([
                CustomerUserPasswordResetType::class => $this->formType
            ], []),
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider submitProvider
     *
     * @param CustomerUser $defaultData
     * @param array $submittedData
     * @param CustomerUser $expectedData
     */
    public function testSubmit($defaultData, array $submittedData, $expectedData)
    {
        $form = $this->factory->create(CustomerUserPasswordResetType::class, $defaultData, []);

        $this->assertEquals($defaultData, $form->getData());
        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitProvider()
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
