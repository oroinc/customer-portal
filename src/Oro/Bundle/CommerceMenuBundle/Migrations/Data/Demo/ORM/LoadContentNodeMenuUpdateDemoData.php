<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use Oro\Bundle\WebCatalogBundle\Migrations\Data\Demo\ORM\LoadWebCatalogDemoData;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads menu updates demo data for content node menu items on the 1st level.
 */
class LoadContentNodeMenuUpdateDemoData extends AbstractMenuUpdateDemoFixture implements DependentFixtureInterface
{
    private ?WebCatalogProvider $webCatalogProvider = null;

    private ?MenuContentNodesProviderInterface $menuContentNodesProvider = null;

    private ?ManagerRegistry $managerRegistry = null;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->webCatalogProvider = $container->get('oro_web_catalog.web_catalog_provider');
        $this->menuContentNodesProvider = $container->get('oro_web_catalog.menu.content_nodes_provider.backoffice');
        $this->managerRegistry = $container->get('doctrine');
    }

    public function load(ObjectManager $manager): void
    {
        $this->createMenuUpdatesForContentNodes($manager);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadWebCatalogDemoData::class,
        ];
    }

    private function createMenuUpdatesForContentNodes(ObjectManager $manager): void
    {
        $rootContentNode = $this->webCatalogProvider->getNavigationRootWithCatalogRootFallback();
        if (!$rootContentNode) {
            return;
        }

        $resolvedRootContentNode = $this->menuContentNodesProvider
            ->getResolvedContentNode($rootContentNode, ['tree_depth' => 1]);

        $itemNumber = $itemConfigNumber = 1;
        $contentNodes = $resolvedRootContentNode->getChildNodes();
        $startingPosition = -100 - count($contentNodes->getKeys());
        $contentNodeEntityManager = $this->managerRegistry->getManagerForClass(ContentNode::class);
        foreach ($contentNodes as $contentNode) {
            $menuUpdateData = [
                'contentNode' => $contentNodeEntityManager->getReference(ContentNode::class, $contentNode->getId()),
                'priority' => $startingPosition++,
                'maxTraverseLevel' => self::ITEM_CONFIGS_BY_ITEM_NUMBER[$itemConfigNumber]['maxTraverseLevel'],
                'menuTemplate' => self::ITEM_CONFIGS_BY_ITEM_NUMBER[$itemConfigNumber]['menuTemplate'],
                'uri' => $this->getLocalizedValue($contentNode->getResolvedContentVariant()->getLocalizedUrls()),
                'title' => $this->getLocalizedValue($contentNode->getTitles()),
            ];
            $menuUpdate = $this->createMenuUpdate($menuUpdateData);
            $manager->persist($menuUpdate);

            $this->setReference('menu_update_content_node_' . $itemNumber, $menuUpdate);

            $itemNumber++;
            $itemConfigNumber = $itemConfigNumber >= 3 ? $itemConfigNumber : $itemConfigNumber + 1;
        }
    }

    protected function createMenuUpdate(array $data): MenuUpdate
    {
        $menuUpdate = parent::createMenuUpdate($data);

        $menuUpdate->setContentNode($data['contentNode']);
        $menuUpdate->setKey('content_node_' . $data['contentNode']->getId());

        return $menuUpdate;
    }
}
