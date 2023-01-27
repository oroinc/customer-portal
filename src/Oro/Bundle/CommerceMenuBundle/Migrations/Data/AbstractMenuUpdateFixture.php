<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Oro\Bundle\AttachmentBundle\Entity\File as AttachmentFile;
use Oro\Bundle\CommerceMenuBundle\Builder\CategoryTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Builder\ContentNodeTreeBuilder;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\DigitalAssetBundle\Entity\DigitalAsset;
use Oro\Bundle\DigitalAssetBundle\Reflector\FileReflector;
use Oro\Bundle\NavigationBundle\Manager\MenuUpdateManager;
use Oro\Bundle\NavigationBundle\MenuUpdate\Propagator\ToMenuUpdate\MenuItemToMenuUpdatePropagatorInterface;
use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;
use Oro\Bundle\NavigationBundle\Provider\MenuUpdateProvider;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Abstract fixture for loading menu updates.
 */
abstract class AbstractMenuUpdateFixture extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected ?PropertyAccessorInterface $propertyAccessor = null;

    protected ?TokenStorageInterface $tokenStorage = null;

    protected ?MenuProviderInterface $menuBuilder = null;

    protected ?MenuUpdateManager $menuUpdateManager = null;

    protected ?ScopeManager $scopeManager = null;

    protected ?FileLocatorInterface $fileLocator = null;

    protected ?FileReflector $fileReflector = null;

    protected ?ItemInterface $menu = null;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;

        if ($this->container) {
            $this->propertyAccessor = $this->container->get('property_accessor');
            $this->tokenStorage = $this->container->get('security.token_storage');
            $this->menuBuilder = $this->container->get('oro_menu.builder_chain');
            $this->menuUpdateManager = $this->container->get('oro_commerce_menu.manager.menu_update');
            $this->scopeManager = $this->container->get('oro_scope.scope_manager');
            $this->fileLocator = $this->container->get('file_locator');
            $this->fileReflector = $this->container->get('oro_digital_asset.reflector.file_reflector');
        }
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getFirstUser($manager);
        if ($user === null) {
            return;
        }

        $previousToken = $this->tokenStorage->getToken();
        $this->setSecurityContext($this->tokenStorage, $user);

        foreach ($this->getData() as $referenceKey => $menuUpdateData) {
            if (!isset($menuUpdateData['targetMenuItem'])) {
                $menuUpdateData['key'] = $menuUpdateData['key'] ?? $referenceKey;
            }

            $menuUpdate = $this->findOrCreateMenuUpdate($menuUpdateData);
            $manager->persist($menuUpdate);

            $this->setReference($referenceKey, $menuUpdate);
        }

        $manager->flush();

        $this->tokenStorage->setToken($previousToken);
    }

    /**
     * @param array $data Menu update data to be passed to MenuUpdateManager::findOrCreateMenuUpdate. Can contain extra
     *  keys:
     *      1) "parentMenuItem" that gives ability to search for parent menu item by category or content node.
     *          Both "parentMenuItem" and "parentKey" cannot be specified at the same time.
     *      2) "targetMenuItem" that gives ability to search for target menu item by category or content node.
     *          Both "targetMenuItem" and "key" cannot be specified at the same time.
     *  [
     *      'targetMenuItem' => [
     *          'contentNode' => '@content_node_reference_name',
     *          // 'category' => '@category_reference_name',
     *      ],
     *      'parentMenuItem' => [
     *          'contentNode' => '@content_node_reference_name',
     *          // 'category' => '@category_reference_name',
     *      ],
     *  ]
     *
     * @return MenuUpdate
     */
    protected function findOrCreateMenuUpdate(array $data): MenuUpdate
    {
        $data = $this->resolveReferences($data);
        $menu = $this->getMenu();

        if (array_key_exists('parentMenuItem', $data)) {
            if (array_key_exists('parentKey', $data)) {
                throw new \LogicException(
                    'Keys "parentMenuItem" and "parentKey" cannot be specified at the same time.'
                );
            }

            $data['parentKey'] = $this->getMenuItemName($menu, $data['parentMenuItem']);
            unset($data['parentMenuItem']);
        }

        if (array_key_exists('targetMenuItem', $data)) {
            if (array_key_exists('key', $data)) {
                throw new \LogicException('Keys "targetMenuItem" and "key" cannot be specified at the same time.');
            }

            $data['key'] = $this->getMenuItemName($menu, $data['targetMenuItem']);

            unset($data['targetMenuItem']);
        }

        $this->handleImage($data);

        $data['propagationStrategy'] = $data['propagationStrategy'] ??
            MenuItemToMenuUpdatePropagatorInterface::STRATEGY_BASIC;

        $menuUpdate = $this->menuUpdateManager->findOrCreateMenuUpdate($menu, $this->getScope(), $data);
        foreach ($data as $key => $value) {
            if ($this->propertyAccessor->isWritable($menuUpdate, $key)) {
                $this->propertyAccessor->setValue($menuUpdate, $key, $value);
            }
        }

        return $menuUpdate;
    }

    protected function resolveReferences(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->resolveReferences($value);
            }

            if (is_string($value) && str_starts_with($value, '@')) {
                $value = substr($value, 1);
                $data[$key] = $this->getReference($value);
            }
        }

        return $data;
    }

    protected function handleImage(array &$data): void
    {
        if (array_key_exists('image', $data)) {
            if ($data['image'] instanceof DigitalAsset) {
                $digitalAsset = $data['image'];
                $data['image'] = new AttachmentFile();
                $this->fileReflector->reflectFromDigitalAsset($data['image'], $digitalAsset);
            } elseif ($data['image'] instanceof AttachmentFile) {
                $sourceFile = $data['image'];
                $data['image'] = new AttachmentFile();
                $this->fileReflector->reflectFromFile($data['image'], $sourceFile);
            }
        }
    }

    protected function getMenuItemName(ItemInterface $menu, array $searchBy): string
    {
        if (isset($searchBy['contentNode'])) {
            $extraKey = MenuUpdate::TARGET_CONTENT_NODE;
            $extraValue = $searchBy['contentNode']->getId();

            // Default name to use if menu item is not found in menu.
            $defaultName = ContentNodeTreeBuilder::getTreeItemNamePrefix($menu, 0) . $extraValue;
        } elseif (isset($searchBy['category'])) {
            $extraKey = MenuUpdate::TARGET_CATEGORY;
            $extraValue = $searchBy['category']->getId();

            // Default name to use if menu item is not found in menu.
            $defaultName = CategoryTreeBuilder::getTreeItemNamePrefix($menu, 0) . $extraValue;
        } else {
            throw new \LogicException('Either "contentNode" or "category" must be specified as search parameter');
        }

        $menuItem = $this->findMenuItemBy($menu, $extraKey, $extraValue);
        if ($menuItem !== null) {
            return $menuItem->getName();
        }

        return $defaultName;
    }

    protected function findMenuItemBy(ItemInterface $menu, string $extraKey, mixed $extraValue): ?ItemInterface
    {
        foreach (MenuUpdateUtils::createRecursiveIterator($menu) as $menuItem) {
            if ($menuItem->getExtra($extraKey)?->getId() === $extraValue) {
                return $menuItem;
            }
        }

        return null;
    }

    protected function getMenu(): ItemInterface
    {
        if ($this->menu === null) {
            $this->menu = $this->menuBuilder->get(
                $this->getMenuName(),
                [
                    BuilderChainProvider::IGNORE_CACHE_OPTION => true,
                    MenuUpdateProvider::SCOPE_CONTEXT_OPTION => $this->getScope(),
                ]
            );
        }

        return $this->menu;
    }

    protected function getMenuName(): string
    {
        return 'commerce_main_menu';
    }

    protected function getScope(): Scope
    {
        return $this->scopeManager->findOrCreate('menu_frontend_visibility', []);
    }

    protected function setSecurityContext(TokenStorageInterface $tokenStorage, User $user): void
    {
        $tokenStorage->setToken(
            new UsernamePasswordOrganizationToken(
                $user,
                $user->getUsername(),
                'main',
                $user->getOrganization(),
                $user->getUserRoles()
            )
        );
    }

    protected function getData(): array
    {
        $fileName = $this->fileLocator->locate($this->getDataPath());
        $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);

        return Yaml::parse(file_get_contents($fileName));
    }

    protected function getDataPath(): string
    {
        throw new \LogicException('Not implemented');
    }

    protected function getFirstUser(ObjectManager $manager): ?User
    {
        $users = $manager->getRepository(User::class)->findBy([], ['id' => 'ASC'], 1);

        return reset($users) ?: null;
    }
}
