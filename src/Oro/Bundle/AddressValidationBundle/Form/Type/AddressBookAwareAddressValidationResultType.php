<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Form\Type;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Utils\AddressBookAddressUtils;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Represents a list of suggested addresses to display for a user.
 * Adds checkboxes to mark to create or update the selected address in Address Book.
 */
class AddressBookAwareAddressValidationResultType extends AbstractType
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['allow_update_address']) {
            $builder->add('update_address', CheckboxType::class);
        }

        if ($options['allow_create_address']) {
            $builder->add('create_address', CheckboxType::class);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('customer_user')
            ->allowedTypes(CustomerUser::class, 'null')
            ->default(null);

        $resolver
            ->define('customer')
            ->allowedTypes(Customer::class, 'null')
            ->default(function (Options $options) {
                return $options['customer_user']?->getCustomer();
            });

        $resolver
            ->define('address_book_new_address_class')
            ->allowedTypes('string', 'null')
            ->allowedValues(CustomerUserAddress::class, CustomerAddress::class, null)
            ->default(function (Options $options) {
                if ($options['customer_user']) {
                    return CustomerUserAddress::class;
                }

                if ($options['customer']) {
                    return CustomerAddress::class;
                }

                return null;
            })
            ->info(
                'Address Book address entity class to use to create a new address in an Address Book. ' .
                'Leave empty to use the first allowed entity class.'
            );

        $resolver
            ->define('allow_update_address')
            ->allowedTypes('boolean')
            ->default(function (Options $options) {
                $addressBookAddress = AddressBookAddressUtils::getAddressBookAddress($options['original_address']);

                return $addressBookAddress !== null && $this->isUpdateGranted($addressBookAddress);
            });

        $resolver
            ->define('allow_create_address')
            ->allowedTypes('boolean')
            ->default(function (Options $options) {
                if ($options['allow_update_address']) {
                    return false;
                }

                if ($options['address_book_new_address_class'] &&
                    $this->isCreateGranted($options['address_book_new_address_class'])) {
                    return true;
                }

                return false;
            });
    }

    private function isUpdateGranted(object $entity): bool
    {
        return $this->authorizationChecker->isGranted(BasicPermission::EDIT, $entity);
    }

    private function isCreateGranted(string $entityClass): bool
    {
        return $this->authorizationChecker->isGranted(BasicPermission::CREATE, 'entity:' . $entityClass);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_address_validation_address_book_aware_validation_result';
    }

    #[\Override]
    public function getParent(): string
    {
        return AddressValidationResultType::class;
    }
}
