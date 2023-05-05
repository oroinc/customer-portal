<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserPasswordRequestType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CustomerUserPasswordRequestTypeTest extends FormIntegrationTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator())
        ];
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        array $options,
        mixed $defaultData,
        mixed $viewData,
        mixed $submittedData,
        mixed $expectedData
    ) {
        $form = $this->factory->create(CustomerUserPasswordRequestType::class, $defaultData, $options);

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitDataProvider(): array
    {
        return [
            'default' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'email' => 'test@test.com'
                ],
                'expectedData' => [
                    'email' => 'test@test.com'
                ]
            ]
        ];
    }
}
