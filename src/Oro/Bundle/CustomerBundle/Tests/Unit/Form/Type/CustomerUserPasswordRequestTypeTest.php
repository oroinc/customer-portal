<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserPasswordRequestType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class CustomerUserPasswordRequestTypeTest extends FormIntegrationTestCase
{
    #[\Override]
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
        mixed $expectedData,
        bool $isValid,
        ?string $errorMessage = null
    ) {
        $form = $this->factory->create(CustomerUserPasswordRequestType::class, $defaultData, $options);

        self::assertEquals($defaultData, $form->getData());
        self::assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        self::assertEquals($isValid, $form->isValid());
        self::assertTrue($form->isSynchronized());
        self::assertEquals($expectedData, $form->getData());

        if (!$isValid) {
            self::assertEquals(1, $form->getErrors(true)->count());
            self::assertEquals($errorMessage, $form->getErrors(true)->current()->getMessage());
        }
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
                ],
                'isValid' => true,
                'errorMessage' => null
            ],
            'invalid data' => [
                'options' => [],
                'defaultData' => [],
                'viewData' => [],
                'submittedData' => [
                    'email' => 't e s t@test.com'
                ],
                'expectedData' => [
                    'email' => 't e s t@test.com'
                ],
                'isValid' => false,
                'errorMessage' => 'This value is not a valid email address.'
            ]
        ];
    }
}
