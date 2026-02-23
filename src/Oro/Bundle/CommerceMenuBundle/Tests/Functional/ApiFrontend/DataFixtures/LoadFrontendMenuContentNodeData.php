<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\ApiFrontend\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Tests\Functional\DataFixtures\MenuUpdateTrait;
use Oro\Bundle\RedirectBundle\Entity\Slug;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\ScopeBundle\Tests\Functional\DataFixtures\LoadScopeData;
use Oro\Bundle\WebCatalogBundle\ContentVariantType\SystemPageContentVariantType;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\ContentVariant;
use Oro\Bundle\WebCatalogBundle\Tests\Functional\DataFixtures\LoadContentNodesData;

/**
 * Creates content variant, slug and menu update for CATALOG_1_ROOT content node with web content scope.
 */
class LoadFrontendMenuContentNodeData extends AbstractFixture implements DependentFixtureInterface
{
    use MenuUpdateTrait;

    public const FRONTEND_MENU_CONTENT_NODE_ITEM = 'frontend_menu_content_node_item';

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadContentNodesData::class,
            LoadWebContentScopeData::class,
            LoadScopeData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Scope $scope */
        $scope = $this->getReference(LoadWebContentScopeData::WEB_CONTENT_SCOPE);

        /** @var ContentNode $contentNode */
        $contentNode = $this->getReference(LoadContentNodesData::CATALOG_1_ROOT);
        $contentNode->addScope($scope);

        $slug = new Slug();
        $slug->setUrl('/' . LoadContentNodesData::CATALOG_1_ROOT);
        $slug->setRouteName('oro_frontend_root');
        $slug->setRouteParameters([]);
        $slug->addScope($scope);
        $slug->setOrganization($contentNode->getWebCatalog()->getOrganization());
        $manager->persist($slug);

        $variant = new ContentVariant();
        $variant->setType(SystemPageContentVariantType::TYPE);
        $variant->setSystemPageRoute('oro_frontend_root');
        $variant->setNode($contentNode);
        $variant->addScope($scope);
        $variant->addSlug($slug);
        $manager->persist($variant);

        $menuUpdate = $this->getMenuUpdate(
            [
                'key' => 'frontend_menu_content_node_item',
                'parent_key' => null,
                'default_title' => 'Content Node Menu Item',
                'titles' => [],
                'default_description' => 'Test menu item with content node',
                'descriptions' => [],
                'content_node' => $contentNode,
                'menu' => 'frontend_menu',
                'scope' => LoadScopeData::DEFAULT_SCOPE,
                'active' => true,
                'priority' => 10,
                'divider' => false,
                'custom' => true
            ],
            MenuUpdate::class
        );

        $this->setReference(self::FRONTEND_MENU_CONTENT_NODE_ITEM, $menuUpdate);
        $manager->persist($menuUpdate);
        $manager->flush();
    }
}
