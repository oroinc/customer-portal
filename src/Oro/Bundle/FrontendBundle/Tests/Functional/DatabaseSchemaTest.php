<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class DatabaseSchemaTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testTableAndSequenceNames()
    {
        /** @var ManagerRegistry $registry */
        $registry = $this->getContainer()->get('doctrine');
        $testedConnections = [];

        /** @var Connection $connection */
        foreach ($registry->getConnections() as $connection) {
            $connectionIdentifier = $this->getConnectionIdentifier($connection);
            if (in_array($connectionIdentifier, $testedConnections, true)) {
                continue;
            }

            $schemaManager = $connection->getSchemaManager();
            $this->assertSchema($schemaManager->createSchema());
            $testedConnections[] = $connectionIdentifier;
        }
    }

    private function getConnectionIdentifier(Connection $connection): string
    {
        return md5(json_encode($connection->getParams(), JSON_THROW_ON_ERROR));
    }

    private function assertSchema(Schema $schema): void
    {
        $tableNames = array_map(
            function (Table $table) {
                return $table->getName();
            },
            $schema->getTables()
        );
        $incorrectTableNames = array_filter(
            $tableNames,
            function ($name) {
                return str_starts_with($name, 'orob2b');
            }
        );
        $this->assertEmpty(
            $incorrectTableNames,
            'Incorrect table names: ' . implode(', ', $incorrectTableNames)
        );

        $sequenceNames = array_map(
            function (Sequence $sequence) {
                return $sequence->getName();
            },
            $schema->getSequences()
        );
        $incorrectSequenceNames = array_filter(
            $sequenceNames,
            function ($name) {
                return str_starts_with($name, 'orob2b');
            }
        );
        $this->assertEmpty(
            $incorrectSequenceNames,
            'Incorrect sequence names: ' . implode(', ', $incorrectSequenceNames)
        );
    }
}
