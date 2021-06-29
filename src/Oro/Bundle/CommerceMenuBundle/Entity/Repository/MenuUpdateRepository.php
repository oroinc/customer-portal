<?php

namespace Oro\Bundle\CommerceMenuBundle\Entity\Repository;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\NavigationBundle\Entity\MenuUpdateInterface;
use Oro\Bundle\NavigationBundle\Entity\Repository\MenuUpdateRepository as BaseMenuUpdateRepository;

/**
 * Repository for MenuUpdate (CommerceMenuBundle) ORM entity.
 */
class MenuUpdateRepository extends BaseMenuUpdateRepository
{
    /**
     * {@inheritDoc}
     * Set uri, contentNode and systemPageRoute of dependent menu updates based on global version.
     *
     * @param MenuUpdateInterface|MenuUpdate $menuUpdate
     */
    public function updateDependentMenuUpdates(MenuUpdateInterface $menuUpdate)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update($this->getEntityName(), 'u')
            ->set('u.uri', ':uri')
            ->set('u.contentNode', ':contentNode')
            ->set('u.systemPageRoute', ':systemPageRoute')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.menu', ':menuName'),
                $qb->expr()->eq('u.key', ':menuUpdateKey'),
                $qb->expr()->neq('u.id', ':currentId')
            ))
            ->setParameter('menuName', $menuUpdate->getMenu())
            ->setParameter('menuUpdateKey', $menuUpdate->getKey())
            ->setParameter('currentId', $menuUpdate->getId())
            ->setParameter('uri', $menuUpdate->getUri())
            ->setParameter('contentNode', $menuUpdate->getContentNode())
            ->setParameter('systemPageRoute', $menuUpdate->getSystemPageRoute());

        $qb->getQuery()->execute();
    }
}