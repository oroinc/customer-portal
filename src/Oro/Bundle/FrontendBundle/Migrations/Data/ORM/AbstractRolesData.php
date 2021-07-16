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

    const ROLES_FILE_NAME = '';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        $roleData = $this->loadRolesData();

        foreach ($roleData as $roleName => $roleConfigData) {
            $role = $this->createEntity($roleName, $roleConfigData['label']);
            $manager->persist($role);

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

    /**
     * @param string $name
     * @param string $label
     *
     * @return AbstractRole
     */
    abstract protected function createEntity($name, $label);

    /**
     * @param array|null $bundles
     *
     * @return array
     */
    protected function loadRolesData(array $bundles = null)
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

    /**
     * @param string $bundle
     *
     * @return string
     */
    protected function getFileName($bundle)
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

    /**
     * @param AclManager                $aclManager
     * @param SecurityIdentityInterface $sid
     * @param array                     $aclData [oid descriptor => [permission, ...], ...]
     */
    protected function setPermissions(AclManager $aclManager, SecurityIdentityInterface $sid, array $aclData)
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

    /**
     * @return AclManager
     */
    protected function getAclManager()
    {
        return $this->container->get('oro_security.acl.manager');
    }
}
