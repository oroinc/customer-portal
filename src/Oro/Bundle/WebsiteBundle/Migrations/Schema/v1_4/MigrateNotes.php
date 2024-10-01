<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\NoteBundle\Migration\UpdateNoteAssociationKindForRenamedEntitiesMigration;

class MigrateNotes extends UpdateNoteAssociationKindForRenamedEntitiesMigration
{
    #[\Override]
    protected function getRenamedEntitiesNames(Schema $schema)
    {
        return [
            'Oro\Bundle\WebsiteBundle\Entity\Website' => 'OroB2B\Bundle\WebsiteBundle\Entity\Website'
        ];
    }
}
