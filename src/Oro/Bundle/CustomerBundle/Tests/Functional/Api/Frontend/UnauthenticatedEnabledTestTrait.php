<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;

trait UnauthenticatedEnabledTestTrait
{
    private int $numberOfVisitors;

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        $this->setApiUnauthenticatedAccessEnabled(false);

        self::assertEquals(
            $this->numberOfVisitors,
            $this->getEntityManager()->getRepository(CustomerVisitor::class)->count([]),
            'New visitors must not be added to the database.'
        );

        parent::tearDown();
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeVisitor(): void
    {
        $this->numberOfVisitors = $this->getEntityManager()->getRepository(CustomerVisitor::class)->count([]);

        $this->setApiUnauthenticatedAccessEnabled(true);
    }

    private function setApiUnauthenticatedAccessEnabled(bool $enabled): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_customer.non_authenticated_visitors_api', $enabled);
        $configManager->flush();
    }
}
