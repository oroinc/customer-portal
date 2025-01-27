<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Functional\Form\Type\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Form\Type\AddressValidatedAtType;
use Oro\Bundle\TestFrameworkBundle\Test\Form\FormAwareTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class AddressValidatedAtTypeTest extends WebTestCase
{
    use FormAwareTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testCreateWithEmptyInitialData(): void
    {
        $form = self::createForm();
        $form->add('validatedAt', AddressValidatedAtType::class);

        self::assertNull($form->getData());
    }

    public function testCreateWithNotEmptyInitialData(): void
    {
        $dateTime = new \DateTime('today');
        $form = self::createForm(FormType::class, ['validatedAt' => $dateTime]);
        $form->add('validatedAt', AddressValidatedAtType::class);

        self::assertEquals(['validatedAt' => $dateTime], $form->getData());
    }

    public function testSubmitWithEmptyDataWhenEmptyInitialData(): void
    {
        $form = self::createForm();
        $form->add('validatedAt', AddressValidatedAtType::class);

        $form->submit([]);

        self::assertTrue($form->isValid(), (string)$form->getErrors(true));
        self::assertTrue($form->isSynchronized());

        self::assertEquals(['validatedAt' => null], $form->getData());
    }

    public function testSubmitWithNotEmptyDataWhenEmptyInitialData(): void
    {
        $dateTime = new \DateTime('01/01/2024 00:00:00');
        $form = self::createForm();
        $form->add('validatedAt', AddressValidatedAtType::class);

        $form->submit(['validatedAt' => '01/01/2024 00:00:00']);

        self::assertTrue($form->isValid(), (string)$form->getErrors(true));
        self::assertTrue($form->isSynchronized());

        self::assertEquals(['validatedAt' => $dateTime], $form->getData());
    }

    public function testSubmitWithEmptyDataWhenNotEmptyInitialData(): void
    {
        $initialDateTime = new \DateTime('01/01/2023 00:00:00');
        $form = self::createForm(FormType::class, ['validatedAt' => $initialDateTime]);
        $form->add('validatedAt', AddressValidatedAtType::class);

        $form->submit(['validatedAt' => '']);

        self::assertTrue($form->isValid(), (string)$form->getErrors(true));
        self::assertTrue($form->isSynchronized());

        self::assertEquals(['validatedAt' => null], $form->getData());
    }

    public function testSubmitWithNotEmptyDataWhenNotEmptyInitialData(): void
    {
        $initialDateTime = new \DateTime('01/01/2023 00:00:00');
        $dateTime = new \DateTime('01/01/2024 00:00:00');
        $form = self::createForm(FormType::class, ['validatedAt' => $initialDateTime]);
        $form->add('validatedAt', AddressValidatedAtType::class);

        $form->submit(['validatedAt' => '01/01/2024 00:00:00']);

        self::assertTrue($form->isValid(), (string)$form->getErrors(true));
        self::assertTrue($form->isSynchronized());

        self::assertEquals(['validatedAt' => $dateTime], $form->getData());
    }

    public function testSubmitWithInvalidDataWhenNotEmptyInitialData(): void
    {
        $initialDateTime = new \DateTime('01/01/2023 00:00:00');
        $form = self::createForm(FormType::class, ['validatedAt' => $initialDateTime]);
        $form->add('validatedAt', AddressValidatedAtType::class);

        $form->submit(['validatedAt' => 'invalid']);

        self::assertFalse($form->isValid());
        self::assertTrue($form->isSynchronized());

        self::assertEquals(['validatedAt' => $initialDateTime], $form->getData());
    }

    public function testHasExtraBlockPrefix(): void
    {
        $form = self::createForm();
        $form->add('validatedAt', AddressValidatedAtType::class);

        $formView = $form->createView();

        self::assertContains(
            'form__oro_address_validation_validated_at',
            $formView['validatedAt']->vars['block_prefixes']
        );
    }
}
