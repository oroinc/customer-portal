<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRoleType;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\UserBundle\Entity\AbstractRole;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * The handler for the storefront that saves customer user role data with privileges to the database.
 */
class CustomerUserRoleUpdateFrontendHandler extends AbstractCustomerUserRoleHandler
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var CustomerUser
     */
    protected $loggedCustomerUser;

    /**
     * @var CustomerUserRole
     */
    protected $predefinedRole;

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param CustomerUserRole $role
     */
    #[\Override]
    public function createForm(AbstractRole $role)
    {
        if ($role->isPredefined()) {
            $this->predefinedRole = $role;
            $role = $this->createNewRole($role);
        }

        return parent::createForm($role);
    }

    protected function processPrivileges(AbstractRole $role)
    {
        if (!$role->isPredefined()) {
            parent::processPrivileges($role);
        }

        $currentPrivileges = $this->getHashedRolePrivileges($this->getRolePrivileges($role));
        $submittedData = json_decode($this->form->get('privileges')->getData(), true);
        $decodedPrivileges = [];

        foreach ($this->privilegeConfig as $field => $config) {
            if (!empty($submittedData[$field])) {
                $decodedPrivileges = array_merge(
                    $decodedPrivileges,
                    $this->decodeAclPrivileges($submittedData[$field], $config)
                );
            }
        }

        $this->setFormPrivilegesGroup($decodedPrivileges, $this->getAclGroup());

        $filteredPrivileges = $this->configurableFilter->filter(
            new ArrayCollection($decodedPrivileges),
            $this->configurableName
        );

        $hashedPrivileges = $this->getHashedRolePrivileges($filteredPrivileges);
        $this->privilegeRepository->savePrivileges(
            $this->aclManager->getSid($role),
            new ArrayCollection(array_replace($currentPrivileges, $hashedPrivileges))
        );

        $this->clearAclCache($role);
    }

    #[\Override]
    protected function createRoleFormInstance(AbstractRole $role, array $privilegeConfig)
    {
        $form = $this->formFactory->create(
            FrontendCustomerUserRoleType::class,
            $role,
            ['privilege_config' => $privilegeConfig, 'predefined_role' => $this->predefinedRole]
        );

        return $form;
    }

    #[\Override]
    protected function getRolePrivileges(AbstractRole $role)
    {
        $sid = $this->aclManager->getSid($this->predefinedRole ?: $role);

        return $this->privilegeRepository->getSupportedAclPrivileges($sid, checkACLSupport: true);
    }

    /**
     * @param CustomerUserRole $role
     * @return CustomerUserRole
     */
    protected function createNewRole(CustomerUserRole $role)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getLoggedUser();

        $newRole = $role->duplicate();

        $newRole
            ->setCustomer($customerUser->getCustomer())
            ->setOrganization($customerUser->getOrganization());

        return $newRole;
    }

    private function getHashedRolePrivileges(ArrayCollection $collection): array
    {
        $collectionArray = $collection->toArray();
        $this->setFormPrivilegesGroup($collectionArray, $this->getAclGroup());

        return array_combine(
            array_map(fn (AclPrivilege $p) => $p->getIdentity()->getId(), $collectionArray),
            $collectionArray
        );
    }

    /**
     * @return CustomerUser
     */
    protected function getLoggedUser()
    {
        if (!$this->loggedCustomerUser) {
            $token = $this->tokenStorage->getToken();

            if ($token) {
                $this->loggedCustomerUser = $token->getUser();
            }
        }

        if (!$this->loggedCustomerUser instanceof CustomerUser) {
            throw new AccessDeniedException();
        }

        return $this->loggedCustomerUser;
    }

    #[\Override]
    protected function filterPrivileges(ArrayCollection $privileges, array $rootIds)
    {
        $privileges = parent::filterPrivileges($privileges, $rootIds);
        $entityPrefix =  'entity:';

        foreach ($privileges as $privilege) {
            $oid = $privilege->getIdentity()->getId();
            if (str_starts_with($oid, $entityPrefix)) {
                $className = substr($oid, \strlen($entityPrefix));

                if ($className === ObjectIdentityFactory::ROOT_IDENTITY_TYPE) {
                    continue;
                }

                $metadata = $this->chainMetadataProvider->getMetadata($className);
                if (!$metadata->hasOwner()) {
                    $privileges->removeElement($privilege);
                }
            }
        }

        return $privileges;
    }

    /**
     * "Success" form handler
     *
     * @param AbstractRole $entity
     * @param User[] $appendUsers
     * @param User[] $removeUsers
     */
    #[\Override]
    protected function onSuccess(AbstractRole $entity, array $appendUsers, array $removeUsers)
    {
        if ($entity instanceof CustomerUserRole) {
            $entity->setSelfManaged(true);
        }

        $this->applyCustomerLimits($entity, $appendUsers, $removeUsers);

        parent::onSuccess($entity, $appendUsers, $removeUsers);
    }

    private function setFormPrivilegesGroup(array $formPrivileges, string $aclGroup): void
    {
        array_walk($formPrivileges, static fn (AclPrivilege $privilege) => $privilege->setGroup($aclGroup));
    }
}
