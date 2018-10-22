<?php

namespace Oro\Bundle\WebsiteBundle\Entity\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\ORM\Repository\BatchIteratorInterface;
use Oro\Bundle\EntityBundle\ORM\Repository\BatchIteratorTrait;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class WebsiteRepository extends EntityRepository implements BatchIteratorInterface
{
    use BatchIteratorTrait;

    /**
     * @return Website[]|Collection
     */
    public function getAllWebsites()
    {
        $websites = $this->createQueryBuilder('website')
            ->addOrderBy('website.id', 'ASC')
            ->getQuery()
            ->getResult();
        $result = [];
        foreach ($websites as $website) {
            $result[$website->getId()] = $website;
        }

        return $result;
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
