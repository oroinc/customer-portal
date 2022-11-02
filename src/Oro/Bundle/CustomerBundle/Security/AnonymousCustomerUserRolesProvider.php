<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Proxy;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * The provider to get roles the storefront anonymous user.
 */
class AnonymousCustomerUserRolesProvider
{
    private WebsiteManager $websiteManager;
    private ManagerRegistry $doctrine;

    public function __construct(WebsiteManager $websiteManager, ManagerRegistry $doctrine)
    {
        $this->websiteManager = $websiteManager;
        $this->doctrine = $doctrine;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $currentWebsite = $this->websiteManager->getCurrentWebsite();
        if (null === $currentWebsite) {
            return [];
        }

        /** @var CustomerUserRole|null $guestRole */
        $guestRole = $currentWebsite->getGuestRole();
        if (null === $guestRole) {
            return [];
        }

        $role = $guestRole instanceof Proxy && !$guestRole->__isInitialized()
            ? $this->loadGuestRole($guestRole->getId())
            : $guestRole->getRole();

        return $role ? [$role] : [];
    }

    /**
     * Loads a guest role name via a simple SQL query.
     * It is cheaper than load a whole CustomerUserRole via lazy loading and get the role name from it,
     * because we load less data and avoid hydration of an entity object.
     */
    private function loadGuestRole(int $roleId): ?string
    {
        $rows = $this->doctrine->getManagerForClass(CustomerUserRole::class)
            ->createQueryBuilder()
            ->select('e.role')
            ->from(CustomerUserRole::class, 'e')
            ->where('e.id = :id')
            ->setParameter('id', $roleId)
            ->getQuery()
            ->getScalarResult();

        return $rows[0]['role'] ?? null;
    }
}
