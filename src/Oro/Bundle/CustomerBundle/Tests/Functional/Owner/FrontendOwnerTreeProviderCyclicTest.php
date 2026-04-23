<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Owner;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Oro\Bundle\CustomerBundle\Owner\FrontendOwnerTreeProvider;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerMutualCycleRelation;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class FrontendOwnerTreeProviderCyclicTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCustomerMutualCycleRelation::class]);
    }

    public function testGetTreeWithCyclicCustomers(): void
    {
        $testHandler = new TestHandler();

        /** @var Logger $logger */
        $logger = $this->client->getContainer()->get('logger');
        $logger->pushHandler($testHandler);

        try {
            $this->getFrontendOwnerTreeProvider()->getTree();
        } finally {
            $logger->popHandler();
        }

        self::assertTrue(
            $testHandler->hasCriticalThatContains('Cyclic relationship in'),
            'Expected critical log about cyclic customer relationship was not emitted.'
        );
    }

    private function getFrontendOwnerTreeProvider(): FrontendOwnerTreeProvider
    {
        return $this->client->getContainer()->get('oro_customer.tests.owner.tree_provider');
    }
}
