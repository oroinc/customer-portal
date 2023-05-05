<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Commerce menu controller for global level.
 *
 * @Route("/menu/frontend/global")
 */
class GlobalMenuController extends AbstractFrontendMenuController
{
    /**
     * @Route("/", name="oro_commerce_menu_global_menu_index")
     * @Template
     *
     * @return array
     */
    public function indexAction()
    {
        return $this->index();
    }

    /**
     * @Route("/{menuName}", name="oro_commerce_menu_global_menu_view")
     * @Template("@OroCommerceMenu/GlobalMenu/update.html.twig")
     *
     * @param string $menuName
     *
     * @return array
     */
    public function viewAction($menuName)
    {
        return $this->update($menuName, null);
    }

    /**
     * @Route("/{menuName}/create/{parentKey}", name="oro_commerce_menu_global_menu_create")
     * @Template("@OroCommerceMenu/GlobalMenu/update.html.twig")
     *
     * @param string      $menuName
     * @param string|null $parentKey
     *
     * @return array|RedirectResponse
     */
    public function createAction($menuName, $parentKey = null)
    {
        return parent::create($menuName, $parentKey);
    }

    /**
     * @Route("/{menuName}/update/{key}", name="oro_commerce_menu_global_menu_update")
     * @Template
     *
     * @return array|RedirectResponse
     */
    public function updateAction(string $menuName, ?string $key = null)
    {
        return parent::update($menuName, $key);
    }

    /**
     * @Route("/{menuName}/move", name="oro_commerce_menu_global_menu_move")
     *
     * @param Request $request
     * @param string  $menuName
     *
     * @return array|RedirectResponse
     */
    public function moveAction(Request $request, $menuName)
    {
        return parent::move($request, $menuName);
    }

    /**
     * {@inheritDoc}
     */
    protected function checkAcl(array $context)
    {
        if (!$this->isGranted('oro_config_system')) {
            throw $this->createAccessDeniedException();
        }
        parent::checkAcl($context);
    }

    /**
     * {@inheritDoc}
     */
    protected function handleUpdate(
        MenuUpdateInterface $menuUpdate,
        array $context,
        ItemInterface $menu
    ): array|RedirectResponse {
        $response = parent::handleUpdate($menuUpdate, $context, $menu);

        // On save RedirectResponse is returned, during rendering response is an array.
        // Perform updates only after update.
        if (!is_array($response)) {
            $this->updateDependentMenuUpdateUrls($menuUpdate);
        }

        return $response;
    }
}
