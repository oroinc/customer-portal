<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Form\Type\Frontend;

use Oro\Bundle\AddressValidationBundle\Form\Type\AddressValidationResultType;
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
 * Adds checkboxes to mark to update the related address in Address Book.
 */
class FrontendAddressBookAwareAddressValidationResultType extends AbstractType
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
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('allow_update_address')
            ->allowedTypes('boolean')
            ->default(function (Options $options) {
                $addressBookAddress = AddressBookAddressUtils::getAddressBookAddress($options['original_address']);

                return $addressBookAddress !== null && $this->isUpdateGranted($addressBookAddress);
            });
    }

    private function isUpdateGranted(object $entity): bool
    {
        return $this->authorizationChecker->isGranted(BasicPermission::EDIT, $entity);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_address_validation_frontend_address_book_aware_validation_result';
    }

    #[\Override]
    public function getParent(): string
    {
        return AddressValidationResultType::class;
    }
}
