<?php

namespace Oro\Bundle\CustomerBundle\Datagrid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Makes grid data filtered by current customer user by default.
 */
class CurrentCustomerUserViewList extends AbstractViewsList
{
    public function __construct(
        TranslatorInterface $translator,
        private TokenAccessorInterface $tokenAccessor
    ) {
        parent::__construct($translator);
    }

    #[\Override]
    protected function getViewsList(): array
    {
        $user = $this->getCustomerUser();
        if (!$user) {
            return [];
        }

        $view = new View(
            'oro_customer.customerUserName',
            [
                'customerUserName' => [
                    'type'  => TextFilterType::TYPE_CONTAINS,
                    'value' => \trim(\sprintf('%s %s', $user->getFirstName(), $user->getLastName())),
                ]
            ]
        );
        $view->setLabel($this->translator->trans('oro.customer.customeruser.entity_label'));
        $view->setDefault(true);

        return [$view];
    }

    private function getCustomerUser(): ?CustomerUser
    {
        $token = $this->tokenAccessor->getToken();

        return $token?->getUser() instanceof CustomerUser ? $token->getUser() : null;
    }
}
