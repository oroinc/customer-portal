<?php

namespace Oro\Bundle\CommerceMenuBundle\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Menu\MenuContentNodesProviderInterface;
use Oro\Bundle\WebCatalogBundle\Provider\WebCatalogProvider;
use Oro\Component\Website\WebsiteInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Adds to menu the 1st level content node from Web Catalog navigation root.
 */
class WebCatalogNavigationRootBuilder implements BuilderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ManagerRegistry $managerRegistry;

    private WebCatalogProvider $webCatalogProvider;

    private MenuContentNodesProviderInterface $menuContentNodesProvider;

    private MenuTemplatesProvider $menuTemplatesProvider;

    private array $extrasOption = [];

    public function __construct(
        ManagerRegistry $managerRegistry,
        WebCatalogProvider $webCatalogProvider,
        MenuContentNodesProviderInterface $menuContentNodesProvider,
        MenuTemplatesProvider $menuTemplatesProvider
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->webCatalogProvider = $webCatalogProvider;
        $this->menuContentNodesProvider = $menuContentNodesProvider;
        $this->menuTemplatesProvider = $menuTemplatesProvider;

        $this->logger = new NullLogger();
    }

    /**
     * Option "extras" to pass to the newly created menu items.
     */
    public function setExtras(array $extrasOption): void
    {
        $this->extrasOption = $extrasOption;
    }

    public function build(ItemInterface $menu, array $options = [], $alias = null): void
    {
        if (!$menu->isDisplayed()) {
            return;
        }

        /** @var WebsiteInterface|null $website */
        $website = $options['website'] ?? null;
        if ($website && !$website instanceof WebsiteInterface) {
            $this->logger->error(
                'Option "website" with value {actual_type} is expected to be {expected_type}',
                ['actual_type' => get_debug_type($website), 'expected_type' => WebsiteInterface::class]
            );
            return;
        }

        $rootContentNode = $this->webCatalogProvider->getNavigationRootWithCatalogRootFallback($website);
        if (!$rootContentNode) {
            // Web catalog is not specified for current scope.
            return;
        }

        $resolvedRootContentNode = $this->menuContentNodesProvider
            ->getResolvedContentNode($rootContentNode, ['tree_depth' => 1]);
        if (!$resolvedRootContentNode) {
            $this->logger->debug(
                'Content node #{node_id} from web catalog #{web_catalog} is not resolved, skipping it.',
                ['node_id' => $rootContentNode->getId(), $rootContentNode->getWebCatalog()->getName()]
            );
            return;
        }

        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass(ContentNode::class);
        $maxNestingLevel = $menu->getExtra('max_nesting_level', 1);
        $maxTraverseLevel = $maxNestingLevel > 0 ? $maxNestingLevel - 1 : 0;
        $menuTemplateName = $this->getFirstAvailableMenuTemplate();

        $childNodes = $resolvedRootContentNode->getChildNodes();
        $startingPosition = -100 - count($childNodes->getKeys());
        foreach ($childNodes->getKeys() as $key) {
            $menu->addChild(
                'content_node_' . $childNodes[$key]->getId(),
                [
                    'extras' => array_merge([
                        'isAllowed' => true,
                        'content_node' => $entityManager->getReference(ContentNode::class, $childNodes[$key]->getId()),
                        'position' => $startingPosition++,
                        'menu_template' => $menuTemplateName,
                        'max_traverse_level' => $maxTraverseLevel,
                        'max_traverse_level_disabled' => false,
                    ], $this->extrasOption),
                ]
            );
        }
    }

    public function getFirstAvailableMenuTemplate(): string
    {
        $menuTemplateNames = array_keys($this->menuTemplatesProvider->getMenuTemplates());

        return reset($menuTemplateNames) ?: '';
    }
}
