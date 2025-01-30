<?php

namespace Oro\Bundle\FrontendBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\UserBundle\Entity\AbstractRole;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * The base class for data fixtures that load default permissions for roles.
 */
abstract class AbstractRolesData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected const ROLES_FILE_NAME = '';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $aclManager = $this->getAclManager();
        $roleData = $this->loadRolesData();

        foreach ($roleData as $roleName => $roleConfigData) {
            $roleLabel = $roleConfigData['label'] ?? null;
            $role = $this->findEntity($manager, $roleName, $roleLabel);
            if (null === $role) {
                $role = $this->createEntity($roleName, $roleLabel);
                $manager->persist($role);
            }

            if ($aclManager->isAclEnabled()) {
                $sid = $aclManager->getSid($role);
                if (!empty($roleConfigData['max_permissions'])) {
                    $this->setPermissionGroup($aclManager, $sid);
                }
                $this->setPermissions($aclManager, $sid, $roleConfigData['permissions'] ?? []);
            }
        }

        $manager->flush();
        if ($aclManager->isAclEnabled()) {
            $aclManager->flush();
        }
    }

    abstract protected function findEntity(ObjectManager $manager, string $name, ?string $label): ?AbstractRole;

    abstract protected function createEntity(string $name, string $label): AbstractRole;

    protected function loadRolesData(?array $bundles = null): array
    {
        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');
        $bundlesForRoles = $bundles ?: array_keys($this->container->getParameter('kernel.bundles'));

        $rolesData = [];
        foreach ($bundlesForRoles as $bundle) {
            $fileName = $this->getFileName($bundle);
            try {
                $file = $kernel->locateResource($fileName);
                $rolesData = array_merge_recursive($rolesData, Yaml::parse(file_get_contents($file)));
            } catch (\InvalidArgumentException $e) {
            }
        }

        return $rolesData;
    }

    protected function getFileName(string $bundle): string
    {
        return sprintf('@%s%s%s', $bundle, '/Migrations/Data/ORM/data/', static::ROLES_FILE_NAME);
    }

    protected function setPermissionGroup(AclManager $aclManager, SecurityIdentityInterface $sid)
    {
        foreach ($aclManager->getAllExtensions() as $extension) {
            $rootOid = $aclManager->getRootOid($extension->getExtensionKey());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                $mask = $maskBuilder->hasMaskForGroup('SYSTEM')
                    ? $maskBuilder->getMaskForGroup('SYSTEM')
                    : $maskBuilder->getMaskForGroup('ALL');
                $aclManager->setPermission($sid, $rootOid, $mask);
            }
        }
    }

    protected function setPermissions(AclManager $aclManager, SecurityIdentityInterface $sid, array $aclData): void
    {
        foreach ($aclData as $oidDescriptor => $permissions) {
            $oid = $aclManager->getOid(str_replace('|', ':', $oidDescriptor));
            $maskBuilders = $aclManager->getAllMaskBuilders($oid);
            foreach ($maskBuilders as $maskBuilder) {
                foreach ($permissions as $permission) {
                    if ($maskBuilder->hasMaskForPermission($permission)) {
                        $maskBuilder->add($permission);
                    }
                }
                $aclManager->setPermission($sid, $oid, $maskBuilder->get());
            }
        }
    }

    protected function getAclManager(): AclManager
    {
        return $this->container->get('oro_security.acl.manager');
    }
}
