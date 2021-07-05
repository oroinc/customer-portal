<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\Repository\MenuUpdateRepository;
use Oro\Bundle\NavigationBundle\Migrations\Data\ORM\UpdateBrokenInheritedUris as BaseUpdateBrokenInheritedUris;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;

/**
 * Sync inherited URIs with global URI.
 */
class UpdateBrokenInheritedUris extends BaseUpdateBrokenInheritedUris
{
    /**
     * @param ObjectManager $manager
     * @return \Doctrine\Persistence\ObjectRepository|MenuUpdateRepository
     */
    protected function getRepository(ObjectManager $manager)
    {
        return $manager->getRepository(MenuUpdate::class);
    }

    /**
     * @param MenuUpdateRepository $repo
     * @return \Iterator|MenuUpdate[]
     */
    protected function getBrokenMenuUpdates(MenuUpdateRepository $repo)
    {
        $scopeManager = $this->container->get(ScopeManager::class);
        $globalScope = $scopeManager->find('menu_frontend_visibility', []);

        $subSelect = $repo->createQueryBuilder('u2');
        $subSelect->select('u2.id')
            ->where(
                $subSelect->expr()->andX(
                    $subSelect->expr()->eq('u.menu', 'u2.menu'),
                    $subSelect->expr()->eq('u.key', 'u2.key'),
                    $subSelect->expr()->neq('u.id', 'u2.id'),
                    $subSelect->expr()->orX(
                        $subSelect->expr()->neq('CAST(u.uri as text)', 'CAST(u2.uri as text)'),
                        $subSelect->expr()->neq('CAST(u.contentNode as int)', 'CAST(u2.contentNode as int)'),
                        $subSelect->expr()->neq('CAST(u.systemPageRoute as text)', 'CAST(u2.systemPageRoute as text)')
                    )
                )
            );

        $qb = $repo->createQueryBuilder('u');
        $qb->where($qb->expr()->exists($subSelect->getDQL()))
            ->andWhere($qb->expr()->eq('u.scope', ':scope'))
            ->setParameter('scope', $globalScope);

        return new BufferedIdentityQueryResultIterator($qb);
    }
}
