<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract fixture for loading menu updated demo data.
 */
abstract class AbstractMenuUpdateDemoFixture extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected const ITEM_CONFIGS_BY_ITEM_NUMBER = [
        1 => [
            'menuTemplate' => 'mega',
            'maxTraverseLevel' => 4,
        ],
        2 => [
            'menuTemplate' => 'tree',
            'maxTraverseLevel' => 3,
        ],
        3 => [
            'menuTemplate' => 'list',
            'maxTraverseLevel' => 1,
        ],
    ];

    protected static $menuName = 'commerce_main_menu';

    private ?ScopeManager $scopeManager = null;

    private ?LocalizationHelper $localizationHelper = null;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->scopeManager = $container->get('oro_scope.scope_manager');
        $this->localizationHelper = $container->get('oro_locale.helper.localization');
    }

    protected function createMenuUpdate(array $data): MenuUpdate
    {
        $menuUpdate = new MenuUpdate();

        $menuUpdate->setMenu(static::$menuName);
        $menuUpdate->setCustom($data['custom'] ?? false);
        $menuUpdate->setScope($data['scope'] ?? $this->getScope());
        $menuUpdate->setUri($data['uri']);
        $menuUpdate->setMaxTraverseLevel($data['maxTraverseLevel'] ?? 0);
        $menuUpdate->setMenuTemplate($data['menuTemplate'] ?? null);
        $menuUpdate->setDefaultTitle($data['title']);
        if (array_key_exists('priority', $data)) {
            $menuUpdate->setPriority($data['priority']);
        }
        if (array_key_exists('parentKey', $data)) {
            $menuUpdate->setParentKey($data['parentKey']);
        }

        return $menuUpdate;
    }

    protected function getScope(): Scope
    {
        return $this->scopeManager->findOrCreate('menu_frontend_visibility', []);
    }

    protected function getLocalizedValue(Collection $values): string
    {
        return $this->localizationHelper->getLocalizedValue($values);
    }
}
