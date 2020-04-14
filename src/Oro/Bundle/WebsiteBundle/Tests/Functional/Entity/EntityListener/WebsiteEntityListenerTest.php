<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Functional\Entity\EntityListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Entity\Website;

class WebsiteEntityListenerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testPrePersist()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $website = new Website();
        $website->setName('test');
        $em->persist($website);
        $em->flush();

        // expect that new scope created
        /** @var ScopeManager $scopeManager */
        $scopeManager = $this->getContainer()->get('oro_scope.scope_manager');
        $scope = $scopeManager->find(ScopeManager::BASE_SCOPE, ['website' => $website]);
        $this->assertNotNull($scope);
        $this->assertSame($website->getId(), $scope->getWebsite()->getId());
    }
}
