<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Doctrine\Persistence\ManagerRegistry;

/**
 * Provides a set of methods to simplify manage of the CustomerVisitor entity.
 */
class CustomerVisitorManager
{
    public function __construct(
        readonly private ManagerRegistry $doctrine
    ) {
    }

    public function findOrCreate(?string $sessionId): CustomerVisitor
    {
        $visitor = $this->find($sessionId);
        if (null === $visitor) {
            $visitor = new CustomerVisitor();
            $visitor->setSessionId($sessionId ?: $this->generateSessionId());
        }

        return $visitor;
    }

    public function find(?string $sessionId): ?CustomerVisitor
    {
        if (!$sessionId) {
            return null;
        }

        return $this->doctrine->getRepository(CustomerVisitor::class)->findOneBy(['sessionId' => $sessionId]);
    }

    public function generateSessionId(): string
    {
        return bin2hex(random_bytes(10));
    }
}
