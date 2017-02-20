<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\NavigationBundle\Controller\AbstractAjaxMenuController;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

/**
 * @Route("/menu/customer")
 */
class CustomerAjaxMenuController extends AbstractAjaxMenuController
{
    /**
     * {@inheritDoc}
     */
    protected function checkAcl(array $context)
    {
        if (!$this->get('oro_security.security_facade')->isGranted(
            'oro_customer_customer_update',
            $context[ScopeCustomerCriteriaProvider::ACCOUNT]
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
        return [ScopeCustomerCriteriaProvider::ACCOUNT, ScopeCriteriaProvider::WEBSITE];
    }

    /**
     * {@inheritDoc}
     */
    protected function getMenuUpdateManager()
    {
        return $this->get('oro_commerce_menu.manager.menu_update');
    }

    /**
     * {@inheritDoc}
     */
    protected function getMenu($menuName, array $context)
    {
        if (array_key_exists(ScopeCustomerCriteriaProvider::ACCOUNT, $context)) {
            /** @var Customer $customer */
            $customer = $context[ScopeCustomerCriteriaProvider::ACCOUNT];
            $context[ScopeOrganizationCriteriaProvider::SCOPE_KEY] = $customer->getOrganization();
            $context[ScopeCustomerGroupCriteriaProvider::FIELD_NAME] = $customer->getGroup();
        }

        return parent::getMenu($menuName, $context);
    }

    /**
     * @Route("/reset/{menuName}", name="oro_commerce_menu_customer_menu_reset")
     * @Method({"DELETE"})
     *
     * {@inheritdoc}
     */
    public function resetAction($menuName, Request $request)
    {
        return parent::resetAction($menuName, $request);
    }

    /**
     * @Route("/create/{menuName}/{parentKey}", name="oro_commerce_menu_customer_menu_ajax_create")
     * @Method({"POST"})
     *
     * {@inheritdoc}
     */
    public function createAction(Request $request, $menuName, $parentKey)
    {
        return parent::createAction($request, $menuName, $parentKey);
    }

    /**
     * @Route("/delete/{menuName}/{key}", name="oro_commerce_menu_customer_menu_delete")
     * @Method({"DELETE"})
     *
     * {@inheritdoc}
     */
    public function deleteAction($menuName, $key, Request $request)
    {
        return parent::deleteAction($menuName, $key, $request);
    }

    /**
     * @Route("/show/{menuName}/{key}", name="oro_commerce_menu_customer_menu_show")
     * @Method({"PUT"})
     *
     * {@inheritdoc}
     */
    public function showAction($menuName, $key, Request $request)
    {
        return parent::showAction($menuName, $key, $request);
    }

    /**
     * @Route("/hide/{menuName}/{key}", name="oro_commerce_menu_customer_menu_hide")
     * @Method({"PUT"})
     *
     * {@inheritdoc}
     */
    public function hideAction($menuName, $key, Request $request)
    {
        return parent::hideAction($menuName, $key, $request);
    }

    /**
     * @Route("/move/{menuName}", name="oro_commerce_menu_customer_menu_move")
     * @Method({"PUT"})
     *
     * {@inheritdoc}
     */
    public function moveAction(Request $request, $menuName)
    {
        return parent::moveAction($request, $menuName);
    }
}
