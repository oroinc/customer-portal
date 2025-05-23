<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ApiFrontend;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;

trait UnauthenticatedEnabledTestTrait
{
    private int $numberOfVisitors;

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
