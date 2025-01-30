<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Provides a set of methods to simplify manage of the CustomerVisitor entity.
 */
class CustomerVisitorManager
{
    public function __construct(
        readonly private ManagerRegistry $doctrine,
        readonly private ?string $writeConnectionName = null
    ) {
    }

    public function findOrCreate(?int $id = null, ?string $sessionId = null): CustomerVisitor
    {
        return $this->find($id, $sessionId) ?: CustomerVisitor::createAnonymous();
    }

    public function find(?int $id = null, ?string $sessionId = null): ?CustomerVisitor
    {
        if (null !== $sessionId && CustomerVisitor::isAnonymousSession($sessionId)) {
            return CustomerVisitor::createAnonymous();
        }
        if (null === $id) {
            return null;
        }

        return $this->getRepository()->findOneBy(['id' => $id, 'sessionId' => $sessionId]);
    }

    public function createUser(): CustomerVisitor
    {
        $connection = $this->getWriteConnection();
        $connection->insert('oro_customer_visitor', [
            'last_visit' => new \DateTime('now', new \DateTimeZone('UTC')),
            'session_id' => self::generateSessionId(),
        ], [
            'last_visit' => Types::DATETIME_MUTABLE,
            'session_id' => Types::STRING,
        ]);
        $id = $connection->lastInsertId('oro_customer_visitor_id_seq');

        return $this->getRepository()->find($id);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrine->getManagerForClass(CustomerVisitor::class);
    }

    private function getRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository(CustomerVisitor::class);
    }

    private function getWriteConnection(): Connection
    {
        if ($this->writeConnectionName &&
            \array_key_exists($this->writeConnectionName, $this->doctrine->getConnectionNames() ?: [])
        ) {
            return $this->doctrine->getConnection($this->writeConnectionName);
        }

        return $this->getEntityManager()->getConnection();
    }

    public static function generateSessionId(): string
    {
        return bin2hex(random_bytes(10));
    }
}
