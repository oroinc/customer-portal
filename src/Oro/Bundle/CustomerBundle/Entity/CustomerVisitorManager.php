<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Provides a set of methods to simplify manage of the CustomerVisitor entity.
 */
class CustomerVisitorManager
{
    private ManagerRegistry $doctrine;
    private ?string $writeConnectionName;
    private ?ConfigManager $configManager = null;

    public function __construct(ManagerRegistry $doctrine, ?string $writeConnectionName = null)
    {
        $this->doctrine = $doctrine;
        $this->writeConnectionName = $writeConnectionName;
    }

    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    /**
     * @param int|null    $id
     * @param string|null $sessionId
     *
     * @return CustomerVisitor
     */
    public function findOrCreate($id = null, $sessionId = null)
    {
        $customerVisitor = $this->find($id, $sessionId);
        if (null !== $customerVisitor) {
            return $customerVisitor;
        }
        if ($this->isAnonymousVisitorEnabled()) {
            $visitor = new CustomerVisitor();
            $visitor->setSessionId($sessionId ?: self::generateSessionId());

            return $visitor;
        }

        return $this->createUser();
    }

    /**
     * @param int|null    $id
     * @param string|null $sessionId
     *
     * @return CustomerVisitor|null
     */
    public function find($id = null, $sessionId = null)
    {
        if (!$sessionId) {
            return null;
        }

        return $this->getRepository()->findOneBy(['sessionId' => $sessionId]);
    }

    private function createUser(): CustomerVisitor
    {
        $connection = $this->getWriteConnection();
        $connection->insert('oro_customer_visitor', [
            'last_visit' => new \DateTime('now', new \DateTimeZone('UTC')),
            'session_id' => $this->generateSessionId(),
        ], [
            'last_visit' => Types::DATETIME_MUTABLE,
            'session_id' => Types::STRING,
        ]);

        $id = $connection->lastInsertId('oro_customer_visitor_id_seq');
        return $this->getRepository()->find($id);
    }

    private function isAnonymousVisitorEnabled(): bool
    {
        return !(bool)$this->configManager->get('oro_customer.create_customer_visitor_immediately');
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
