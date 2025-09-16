<?php

namespace Oro\Bundle\CommerceMenuBundle\Controller;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Commerce menu controller for global level.
 */
#[Route(path: '/menu/frontend/global')]
class GlobalMenuController extends AbstractFrontendMenuController
{
    /**
     *
     * @return array
     */
    #[Route(path: '/', name: 'oro_commerce_menu_global_menu_index')]
    #[Template('@OroCommerceMenu/GlobalMenu/index.html.twig')]
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('oro_navigation_manage_menus');

        return $this->index();
    }

    /**
     *
     * @param string $menuName
     *
     * @return array
     */
    #[Route(path: '/{menuName}', name: 'oro_commerce_menu_global_menu_view')]
    #[Template('@OroCommerceMenu/GlobalMenu/update.html.twig')]
    public function viewAction($menuName)
    {
        return $this->update($menuName, null);
    }

    /**
     *
     * @param string      $menuName
     * @param string|null $parentKey
     *
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/create/{parentKey}', name: 'oro_commerce_menu_global_menu_create')]
    #[Template('@OroCommerceMenu/GlobalMenu/update.html.twig')]
    public function createAction($menuName, $parentKey = null)
    {
        return parent::create($menuName, $parentKey);
    }

    /**
     *
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/update/{key}', name: 'oro_commerce_menu_global_menu_update')]
    #[Template('@OroCommerceMenu/GlobalMenu/update.html.twig')]
    public function updateAction(string $menuName, ?string $key = null)
    {
        return parent::update($menuName, $key);
    }

    /**
     *
     * @param Request $request
     * @param string  $menuName
     * @return array|RedirectResponse
     */
    #[Route(path: '/{menuName}/move', name: 'oro_commerce_menu_global_menu_move')]
    public function moveAction(Request $request, $menuName)
    {
        return parent::move($request, $menuName);
    }

    #[\Override]
    protected function checkAcl(array $context)
    {
        if (!$this->isGranted('oro_config_system')) {
            throw $this->createAccessDeniedException();
        }
        parent::checkAcl($context);
    }

    #[\Override]
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
