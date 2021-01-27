<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;

class LoadCustomerVisitors extends AbstractFixture
{
    const CUSTOMER_VISITOR = 'customer_visitor';
    const CUSTOMER_VISITOR_EXPIRED = 'customer_visitor_expired';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->createCustomerVisitor($manager, self::CUSTOMER_VISITOR);

        $lastVisit = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastVisit->modify('-30 days');
        $this->createCustomerVisitor($manager, self::CUSTOMER_VISITOR_EXPIRED, $lastVisit);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $reference
     * @param \DateTime|null $lastVisit
     *
     * @return CustomerVisitor
     */
    private function createCustomerVisitor(ObjectManager $manager, $reference, $lastVisit = null)
    {
        $anonymous = new CustomerVisitor();
        if ($lastVisit) {
            $anonymous->setLastVisit($lastVisit);
        }
        $anonymous->setSessionId(md5(time()));

        $manager->persist($anonymous);
        $this->addReference($reference, $anonymous);

        return $anonymous;
    }
}
