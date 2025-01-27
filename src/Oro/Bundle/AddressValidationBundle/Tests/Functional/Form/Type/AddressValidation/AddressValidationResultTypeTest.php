<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Functional\Form\Type\AddressValidation;

use Oro\Bundle\AddressValidationBundle\Form\Type\AddressValidationResultType;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\ResolvedAddressAcceptorInterface;
use Oro\Bundle\AddressValidationBundle\Test\ResolvedAddressAwareTestTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerAddresses;
use Oro\Bundle\TestFrameworkBundle\Test\Form\FormAwareTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddressValidationResultTypeTest extends WebTestCase
{
    use FormAwareTestTrait;
    use ResolvedAddressAwareTestTrait;

    private ResolvedAddressAcceptorInterface $resolvedAddressAcceptor;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerAddresses::class]);

        $this->resolvedAddressAcceptor = self::getContainer()->get('oro_address_validation.resolved_address.acceptor');
    }

    public function testHasFields(): void
    {
        $originalAddress = $this->getReference('customer.level_1.address_1');
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference('customer.level_1.address_2'), $originalAddress),
            self::createResolvedAddress($this->getReference('customer.level_1.address_3'), $originalAddress),
        ];

        $form = self::createForm(
            AddressValidationResultType::class,
            null,
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertNull($form->getConfig()->getOption('data_class'));
        self::assertSame([], $form->getConfig()->getOption('validation_groups'));

        self::assertFormHasField(
            $form,
            'address',
            ChoiceType::class,
            [
                'expanded' => true,
                'multiple' => false,
                'inherit_data' => false,
                'choices' => [$originalAddress, ...$suggestedAddresses],
            ]
        );
    }

    public function testSubmit(): void
    {
        $originalAddress = $this->getReference('customer.level_1.address_1');
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference('customer.level_1.address_2'), $originalAddress),
            self::createResolvedAddress($this->getReference('customer.level_1.address_3'), $originalAddress),
        ];

        $form = self::createForm(
            AddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertEquals(['address' => $originalAddress], $form->getData());

        $form->submit(['address' => '1']);

        $expectedAddress = $this->resolvedAddressAcceptor->acceptResolvedAddress($suggestedAddresses[0]);

        self::assertTrue($form->isValid());
        self::assertEquals(['address' => $expectedAddress], $form->getData());
    }

    public function testGetBlockPrefix(): void
    {
        $originalAddress = $this->getReference('customer.level_1.address_1');
        $suggestedAddresses = [
            self::createResolvedAddress($this->getReference('customer.level_1.address_2'), $originalAddress),
            self::createResolvedAddress($this->getReference('customer.level_1.address_3'), $originalAddress),
        ];

        $form = self::createForm(
            AddressValidationResultType::class,
            ['address' => $originalAddress],
            [
                'original_address' => $originalAddress,
                'suggested_addresses' => $suggestedAddresses,
            ]
        );

        self::assertContains('oro_address_validation_result', $form->createView()->vars['block_prefixes']);
    }
}
