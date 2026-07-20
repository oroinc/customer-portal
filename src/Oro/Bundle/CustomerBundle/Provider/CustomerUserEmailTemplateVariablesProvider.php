<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Twig\Sandbox\EntityVariablesProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides email template variables for the {@see CustomerUser} entity.
 *
 * Exposes the computed "fullName" virtual field (backed by {@see CustomerUser::getFullName()})
 * that is not mapped as an ORM column and therefore cannot be registered automatically
 * through entity field configuration.
 */
class CustomerUserEmailTemplateVariablesProvider implements EntityVariablesProviderInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function getVariableDefinitions(): array
    {
        return [
            CustomerUser::class => [
                'fullName' => [
                    'type'  => 'string',
                    'label' => $this->translator->trans('oro.customer.customeruser.full_name'),
                ],
            ],
        ];
    }

    #[\Override]
    public function getVariableGetters(): array
    {
        return [
            CustomerUser::class => [
                'fullName' => 'getFullName',
            ],
        ];
    }

    #[\Override]
    public function getVariableProcessors(string $entityClass): array
    {
        return [];
    }
}
