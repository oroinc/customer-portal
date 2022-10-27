<?php

namespace Oro\Bundle\WebsiteBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\Repository\BatchIteratorInterface;
use Oro\Bundle\EntityBundle\ORM\Repository\BatchIteratorTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Entity repository for Website dictionary.
 */
class WebsiteRepository extends EntityRepository implements BatchIteratorInterface
{
    use BatchIteratorTrait;

    /**
     * @param Organization $organization
     *
     * @return Website[]
     */
    public function getAllWebsites(Organization $organization = null)
    {
        $qb = $this->createQueryBuilder('website');
        // Join organization to website as it will be immediately accessed during config calls.
        $qb->addSelect('org')
            ->join('website.organization', 'org');
        $qb->addOrderBy('website.id', 'ASC');

        if ($organization) {
            $qb->where($qb->expr()->eq('website.organization', ':organization'))
                ->setParameter('organization', $organization);
        }

        /** @var Website[] $websites */
        $websites = $qb->getQuery()->getResult();

        $result = [];
        foreach ($websites as $website) {
            $result[$website->getId()] = $website;
        }

        return $result;
    }

    public function getWebsitesNotInList(array $skipIds, Organization $organization = null): \Iterator
    {
        $qb = $this->createQueryBuilder('website');

        if ($skipIds) {
            $qb->where($qb->expr()->notIn('website.id', ':ids'))
                ->setParameter('ids', $skipIds);
        }
        if ($organization) {
            $qb->where($qb->expr()->eq('website.organization', ':organization'))
                ->setParameter('organization', $organization);
        }

        return new BufferedIdentityQueryResultIterator($qb);
    }

    /**
     * @param Organization|null $organization
     *
     * @return int[]
     */
    public function getAllWebsitesIds(?Organization $organization = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('website.id')
            ->from($this->getEntityName(), 'website')
            ->addOrderBy('website.id', 'ASC');

        if ($organization) {
            $qb
                ->where($qb->expr()->eq('website.organization', ':organization'))
                ->setParameter('organization', $organization);
        }

        $result = $qb->getQuery()->getResult();

        return array_map(fn ($value) => (int)$value['id'], $result);
    }

    /**
     * @return Website
     */
    public function getDefaultWebsite()
    {
        return $this->findOneBy(['default' => true]);
    }

    /**
     * @return int[]
     */
    public function getWebsiteIdentifiers()
    {
        $rows = $this->createQueryBuilder('website')
            ->select('website.id')
            ->getQuery()
            ->getArrayResult();

        return array_column($rows, 'id');
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function checkWebsiteExists($websiteId)
    {
        return $this->find($websiteId) instanceof Website;
    }

    /**
     * @param int|null $id
     * @return Website[]
     */
    public function getByIdOrAll($id = null)
    {
        if ($id) {
            return ($website = $this->find($id)) ? [$website] : [];
        }

        return $this->findAll();
    }
}
