<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;

class LoadCustomerVisitors extends AbstractFixture
{
    public const string CUSTOMER_VISITOR = 'customer_visitor';
    public const string CUSTOMER_VISITOR_EXPIRED = 'customer_visitor_expired';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->createCustomerVisitor($manager, self::CUSTOMER_VISITOR, null);

        $lastVisit = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastVisit->modify('-30 days');
        $this->createCustomerVisitor($manager, self::CUSTOMER_VISITOR_EXPIRED, $lastVisit);

        $manager->flush();
    }

    private function createCustomerVisitor(ObjectManager $manager, string $reference, ?\DateTime $lastVisit): void
    {
        $anonymous = new CustomerVisitor();
        if ($lastVisit) {
            $anonymous->setLastVisit($lastVisit);
        }
        $manager->persist($anonymous);
        $this->addReference($reference, $anonymous);
    }
}
