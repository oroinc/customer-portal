<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Store author's name in the owner_description field
 */
class UpdateOwnerDescription extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /** {@inheritdoc} */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /** {@inheritdoc} */
    public function load(ObjectManager $manager)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->container->get('doctrine');

        /** @var Connection $connection */
        $connection = $doctrine->getConnection();

        $statement = $connection->prepare('SELECT min(id) as min FROM oro_audit');
        $statement->execute();
        $min = $statement->fetchColumn(0);
        $statement = $connection->prepare('SELECT max(id) as max FROM oro_audit');
        $statement->execute();
        $max = $statement->fetchColumn(0);

        $ownerWithQuery = <<<SQL
UPDATE oro_audit
SET owner_description = (SELECT CONCAT(oro_customer_user.first_name, ' ', oro_customer_user.last_name, ' - ',
                                       oro_customer_user.email)
                         FROM oro_customer_user
                         WHERE oro_customer_user.id = oro_audit.customer_user_id
                         LIMIT 1)
WHERE owner_description IS NULL
  AND id >= :min
  AND id < :max
  AND customer_user_id IS NOT NULL;
SQL;
        $ownerWithStatement = $connection->prepare($ownerWithQuery);

        $current = $min;
        $delta = 10000;

        $connection->commit();

        while ($current < $max) {
            $connection->beginTransaction();
            try {
                $next = $current + $delta;
                $ownerWithStatement->execute(['min' => $current, 'max' => $next]);
                $current = $next;
                $connection->commit();
            } catch (\Throwable $t) {
                $connection->rollBack();
            }
        }

        $connection->beginTransaction();
    }
}
