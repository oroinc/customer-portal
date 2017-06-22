<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\NavigationBundle\Controller\AbstractAjaxMenuController;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

/**
 * @Route("/menu/customer-group")
 */
class CustomerGroupAjaxMenuController extends AbstractAjaxMenuController
{
    /**
     * {@inheritDoc}
     */
    protected function checkAcl(array $context)
    {
        if (!$this->isGranted(
            'oro_customer_account_group_update',
            $context[ScopeCustomerGroupCriteriaProvider::FIELD_NAME]
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
        return [ScopeCustomerGroupCriteriaProvider::FIELD_NAME, ScopeCriteriaProvider::WEBSITE];
    }

    /**
     * {@inheritDoc}
     */
    protected function getMenu($menuName, array $context)
    {
        if (array_key_exists(ScopeCustomerGroupCriteriaProvider::FIELD_NAME, $context)) {
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $context[ScopeCustomerGroupCriteriaProvider::FIELD_NAME];
            $context[ScopeOrganizationCriteriaProvider::SCOPE_KEY] = $customerGroup->getOrganization();
        }

        return parent::getMenu($menuName, $context);
    }

    /**
     * {@inheritDoc}
     */
    protected function getMenuUpdateManager()
    {
        return $this->get('oro_commerce_menu.manager.menu_update');
    }

    /**
     * @Route("/reset/{menuName}", name="oro_commerce_menu_customer_group_menu_ajax_reset")
     * @Method({"DELETE"})
     *
     * {@inheritdoc}
     */
    public function resetAction($menuName, Request $request)
    {
        return parent::resetAction($menuName, $request);
    }

    /**
     * @Route("/create/{menuName}/{parentKey}", name="oro_commerce_menu_customer_group_menu_ajax_create")
     * @Method({"POST"})
     *
     * {@inheritdoc}
     */
    public function createAction(Request $request, $menuName, $parentKey)
    {
        return parent::createAction($request, $menuName, $parentKey);
    }

    /**
     * @Route("/delete/{menuName}/{key}", name="oro_commerce_menu_customer_group_menu_ajax_delete")
     * @Method({"DELETE"})
     *
     * {@inheritdoc}
     */
    public function deleteAction($menuName, $key, Request $request)
    {
        return parent::deleteAction($menuName, $key, $request);
    }

    /**
     * @Route("/show/{menuName}/{key}", name="oro_commerce_menu_customer_group_menu_ajax_show")
     * @Method({"PUT"})
     *
     * {@inheritdoc}
     */
    public function showAction($menuName, $key, Request $request)
    {
        return parent::showAction($menuName, $key, $request);
    }

    /**
     * @Route("/hide/{menuName}/{key}", name="oro_commerce_menu_customer_group_menu_ajax_hide")
     * @Method({"PUT"})
     *
     * {@inheritdoc}
     */
    public function hideAction($menuName, $key, Request $request)
    {
        return parent::hideAction($menuName, $key, $request);
    }

    /**
     * @Route("/move/{menuName}", name="oro_commerce_menu_customer_group_menu_ajax_move")
     * @Method({"PUT"})
     *
     * {@inheritdoc}
     */
    public function moveAction(Request $request, $menuName)
    {
        return parent::moveAction($request, $menuName);
    }
}
