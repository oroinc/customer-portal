<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Provides a set of methods to simplify manage of the CustomerVisitor entity.
 */
class CustomerVisitorManager
{
    /** @var ManagerRegistry */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param int|null    $id
     * @param string|null $sessionId
     *
     * @return CustomerVisitor
     */
    public function findOrCreate($id = null, $sessionId = null)
    {
        $user = $this->find($id, $sessionId);

        if (null === $user) {
            return $this->createUser();
        }

        return $user;
    }

    /**
     * @param int|null    $id
     * @param string|null $sessionId
     *
     * @return CustomerVisitor|null
     */
    public function find($id = null, $sessionId = null)
    {
        if (null === $id) {
            return null;
        }

        return $this->getRepository()->findOneBy(['id' => $id, 'sessionId' => $sessionId]);
    }

    /**
     * @param CustomerVisitor $user
     * @param int             $updateLatency
     */
    public function updateLastVisitTime(CustomerVisitor $user, $updateLatency)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if ($updateLatency < $now->getTimestamp() - $user->getLastVisit()->getTimestamp()) {
            $this->getEntityManager()
                ->createQueryBuilder()
                ->update(CustomerVisitor::class, 'v')
                ->set('v.lastVisit', ':lastVisit')
                ->where('v.id = :id')
                ->setParameter('lastVisit', $now, Types::DATETIME_MUTABLE)
                ->setParameter('id', $user->getId())
                ->getQuery()
                ->execute();
        }
    }

    /**
     * @return CustomerVisitor
     */
    private function createUser()
    {
        $user = new CustomerVisitor;

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->doctrine->getManagerForClass(CustomerVisitor::class);
    }

    /**
     * @return EntityRepository
     */
    private function getRepository()
    {
        return $this->getEntityManager()->getRepository(CustomerVisitor::class);
    }
}
