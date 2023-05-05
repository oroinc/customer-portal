<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Entity\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class CustomerVisitorManagerTest extends WebTestCase
{
    private function getDoctrine(): ManagerRegistry
    {
        return self::getContainer()->get('doctrine');
    }

    public function testCreateWithDefaultConnection()
    {
        $manager = new CustomerVisitorManager($this->getDoctrine());
        $this->assertInstanceOf(CustomerVisitor::class, $manager->findOrCreate());
    }

    public function testCreateWithSessionConnection()
    {
        $manager = new CustomerVisitorManager($this->getDoctrine(), 'session');
        $this->assertInstanceOf(CustomerVisitor::class, $manager->findOrCreate());
    }
}
