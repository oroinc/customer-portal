<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\OrganizationBundle\Provider\ScopeOrganizationCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

/**
 * @Route("/menu/customer-group")
 */
class CustomerGroupMenuController extends AbstractFrontendMenuController
{
    /**
     * @Route("/{id}", name="oro_commerce_menu_customer_group_menu_index")
     * @Template
     *
     * @param CustomerGroup $customerGroup
     * @return array
     */
    public function indexAction(CustomerGroup $customerGroup)
    {
        $contexts = $this->get('oro_commerce_menu.menu_context_provider.customer_group')->getContexts($customerGroup);

        return [
            'entity' => $customerGroup,
            'contexts' => $contexts,
            'entityClass' => $this->getEntityClass(),
        ];
    }

    /**
     * @Route(
     *      "/context/",
     *      name="oro_commerce_menu_customer_group_menu_context_index",
     *      requirements={"id"="\d+"}
     * )
     * @Template("OroCommerceMenuBundle:CustomerGroupMenu/widget:contextIndex.html.twig")
     * @param Request $request
     * @return array
     */
    public function contextIndexAction(Request $request)
    {
        $this->checkAcl();
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::index($context);
    }

    /**
     * @Route("/{menuName}/view", name="oro_commerce_menu_customer_group_menu_view")
     * @Template
     *
     * @param string  $menuName
     * @param Request $request
     * @return array
     */
    public function viewAction(Request $request, $menuName)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::view($menuName, $context);
    }

    /**
     * @Route("/{menuName}/create/{parentKey}", name="oro_commerce_menu_customer_group_menu_create")
     * @Template("OroCommerceMenuBundle:CustomerGroupMenu:update.html.twig")
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
     * @Route("/{menuName}/update/{key}", name="oro_commerce_menu_customer_group_menu_update")
     * @Template
     *
     * @param Request $request
     * @param string  $menuName
     * @param string  $key
     * @return array|RedirectResponse
     */
    public function updateAction(Request $request, $menuName, $key)
    {
        $context = $this->getContextFromRequest($request, $this->getAllowedContextKeys());

        return parent::update($menuName, $key, $context);
    }

    /**
     * {@inheritDoc}
     */
    protected function checkAcl()
    {
        if (!$this->get('oro_security.security_facade')->isGranted('oro_customer_account_group_update')) {
            throw $this->createAccessDeniedException();
        }
        parent::checkAcl();
    }

    /**
     * @return array
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
//            $context[ScopeOrganizationCriteriaProvider::SCOPE_KEY] = $customerGroup->getOrganization();
        }

        return parent::getMenu($menuName, $context);
    }
}
