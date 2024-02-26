<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\NavigationBundle\Controller\AbstractAjaxMenuController;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The AJAX controller for the customer group menu.
 */
#[Route(path: '/menu/customer-group')]
#[CsrfProtection()]
class CustomerGroupAjaxMenuController extends AbstractAjaxMenuController
{
    /**
     * {@inheritDoc}
     */
    protected function checkAcl(array $context)
    {
        if (!$this->isGranted(
            'oro_customer_account_group_update',
            $context[ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP]
        )
        ) {
            throw $this->createAccessDeniedException();
        }
        parent::checkAcl($context);
    }

    /**
     * {@inheritDoc}
     */
    protected function getAllowedContextKeys()
    {
        return [ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP, ScopeCriteriaProvider::WEBSITE];
    }

    /**
     * {@inheritDoc}
     */
    protected function getMenu($menuName, array $context)
    {
        if (array_key_exists(ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP, $context)) {
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $context[ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP];
            $context[ScopeOrganizationCriteriaProvider::ORGANIZATION] = $customerGroup->getOrganization();
        }

        return parent::getMenu($menuName, $context);
    }

    /**
     * {@inheritdoc}
     */
    #[Route(path: '/reset/{menuName}', name: 'oro_commerce_menu_customer_group_menu_ajax_reset', methods: ['DELETE'])]
    public function resetAction($menuName, Request $request)
    {
        return parent::resetAction($menuName, $request);
    }

    /**
     * {@inheritdoc}
     */
    #[Route(
        path: '/create/{menuName}/{parentKey}',
        name: 'oro_commerce_menu_customer_group_menu_ajax_create',
        methods: ['POST']
    )]
    public function createAction(Request $request, $menuName, $parentKey)
    {
        return parent::createAction($request, $menuName, $parentKey);
    }

    /**
     * {@inheritdoc}
     */
    #[Route(
        path: '/delete/{menuName}/{key}',
        name: 'oro_commerce_menu_customer_group_menu_ajax_delete',
        methods: ['DELETE']
    )]
    public function deleteAction($menuName, $key, Request $request)
    {
        return parent::deleteAction($menuName, $key, $request);
    }

    /**
     * {@inheritdoc}
     */
    #[Route(path: '/show/{menuName}/{key}', name: 'oro_commerce_menu_customer_group_menu_ajax_show', methods: ['PUT'])]
    public function showAction($menuName, $key, Request $request)
    {
        return parent::showAction($menuName, $key, $request);
    }

    /**
     * {@inheritdoc}
     */
    #[Route(path: '/hide/{menuName}/{key}', name: 'oro_commerce_menu_customer_group_menu_ajax_hide', methods: ['PUT'])]
    public function hideAction($menuName, $key, Request $request)
    {
        return parent::hideAction($menuName, $key, $request);
    }

    /**
     * {@inheritdoc}
     */
    #[Route(path: '/move/{menuName}', name: 'oro_commerce_menu_customer_group_menu_ajax_move', methods: ['PUT'])]
    public function moveAction(Request $request, $menuName)
    {
        return parent::moveAction($request, $menuName);
    }
}
