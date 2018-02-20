<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/menu/customer")
 */
class CustomerMenuController extends AbstractFrontendMenuController
{
    /**
     * @Route("/{id}", name="oro_commerce_menu_customer_menu_index")
     * @Template
     *
     * @param Customer $customer
     * @return array
     */
    public function indexAction(Customer $customer)
    {
        $contexts = $this->get('oro_commerce_menu.menu_context_provider.customer')->getContexts($customer);

        return [
            'entity' => $customer,
            'contexts' => $contexts,
            'entityClass' => $this->getEntityClass(),
        ];
    }

    /**
     * @Route(
     *      "/context/",
     *      name="oro_commerce_menu_customer_menu_context_index",
     *      requirements={"id"="\d+"}
     * )
     * @Template("OroCommerceMenuBundle:CustomerMenu/widget:contextIndex.html.twig")
     * @param Request $request
     * @return array
     */
    public function contextIndexAction(Request $request)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());
        $this->checkAcl($context);

        return parent::index($context);
    }

    /**
     * @Route("/{menuName}/view", name="oro_commerce_menu_customer_menu_view")
     * @Template
     *
     * @param string  $menuName
     * @param Request $request
     * @return array
     */
    public function viewAction($menuName, Request $request)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::view($menuName, $context);
    }

    /**
     * @Route("/{menuName}/create/{parentKey}", name="oro_commerce_menu_customer_menu_create")
     * @Template("OroCommerceMenuBundle:CustomerMenu:update.html.twig")
     *
     * @param Request     $request
     * @param string      $menuName
     * @param string|null $parentKey
     * @return array|RedirectResponse
     */
    public function createAction(Request $request, $menuName, $parentKey = null)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::create($menuName, $parentKey, $context);
    }

    /**
     * @Route("/{menuName}/update/{key}", name="oro_commerce_menu_customer_menu_update")
     * @Template
     *
     * @param string  $menuName
     * @param string  $key
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function updateAction($menuName, $key, Request $request)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::update($menuName, $key, $context);
    }

    /**
     * @Route("/{menuName}/move", name="oro_commerce_menu_customer_menu_move")
     *
     * @param Request $request
     * @param string  $menuName
     *
     * @return array|RedirectResponse
     */
    public function moveAction(Request $request, $menuName)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::move($request, $menuName, $context);
    }

    /**
     * {@inheritDoc}
     */
    protected function checkAcl(array $context)
    {
        if (!$this->isGranted(
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
     * @return array
     */
    protected function getAllowedContextKeys()
    {
        return [ScopeCustomerCriteriaProvider::ACCOUNT, ScopeCriteriaProvider::WEBSITE];
    }
}
