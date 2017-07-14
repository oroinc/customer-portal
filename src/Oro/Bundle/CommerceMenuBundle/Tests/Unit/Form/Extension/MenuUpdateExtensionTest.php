<?php

namespace Oro\Bundle\CustomerMenuBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Form\PreloadedExtension;

use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\ImageTypeStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\MenuUpdateTypeStub;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\MenuUpdateExpressionValidator;
use Oro\Bundle\NavigationBundle\Validator\Constraints\MaxNestedLevelValidator;
use Oro\Bundle\CommerceMenuBundle\Form\Extension\MenuUpdateExtension;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Component\Testing\Unit\FormIntegrationTestCase;

class MenuUpdateExtensionTest extends FormIntegrationTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $configProvider = $this->createMock(ConfigProvider::class);

        $translator = $this->createMock(Translator::class);

        return [
            new PreloadedExtension(
                [
                    new ImageTypeStub,
                ],
                [
                    MenuUpdateTypeStub::class => [new MenuUpdateExtension()],
                    FormType::class => [new TooltipFormExtension($configProvider, $translator)]
                ]
            ),
            $this->getValidatorExtension(true)
        ];
    }

    public function testSubmitValid()
    {
        $menuUpdate = new MenuUpdateStub();
        $form = $this->factory->create(MenuUpdateTypeStub::class, $menuUpdate);

        $form->submit(
            [
                'uri' => 'localhost',
                'image' => 'image.png',
                'condition' => 'false'
            ]
        );

        $expected = new MenuUpdateStub();
        $expected->setUri('localhost');
        $expected->setCondition('false');
        // TODO fix it
        $expected->setImage('image.png');

        $this->assertFormIsValid($form);
        $this->assertEquals($expected, $form->getData());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConstraintValidatorFactoryInterface
     */
    protected function getConstraintValidatorFactory()
    {
        /* @var $factory \PHPUnit_Framework_MockObject_MockObject|ConstraintValidatorFactoryInterface */
        $factory = $this->createMock('Symfony\Component\Validator\ConstraintValidatorFactoryInterface');

        $mockedValidators = [MaxNestedLevelValidator::class, MenuUpdateExpressionValidator::class];

        $factory->expects($this->any())
            ->method('getInstance')
            ->willReturnCallback(
                function (Constraint $constraint) use ($mockedValidators) {
                    $className = $constraint->validatedBy();

                    foreach ($mockedValidators as $mockedValidator) {
                        $this->validators[$className] = $this->getMockBuilder($mockedValidator)
                            ->disableOriginalConstructor()
                            ->getMock();
                    }

                    if (!isset($this->validators[$className]) ||
                        $className === 'Symfony\Component\Validator\Constraints\CollectionValidator'
                    ) {
                        $this->validators[$className] = new $className();
                    }

                    return $this->validators[$className];
                }
            );

        return $factory;
    }
}
