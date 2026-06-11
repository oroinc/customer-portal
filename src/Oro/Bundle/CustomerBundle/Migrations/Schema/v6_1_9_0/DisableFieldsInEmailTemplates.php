<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v6_1_9_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\Audit;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Migration\SetEmailAvailableInTemplateQuery;
use Oro\Bundle\EmailBundle\Migration\SetEmailImmutableQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Disables fields that should not be available in email templates.
 *  Marks sensitive fields as immutable to prevent modification through the UI.
 */
class DisableFieldsInEmailTemplates implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addQuery(
            new SetEmailAvailableInTemplateQuery(
                entityClass: CustomerUser::class,
                availableInTemplate: false,
                fieldNames: ['settings'],
                immutable: true
            )
        );
        $queries->addQuery(
            new SetEmailImmutableQuery(
                entityClass: CustomerUser::class,
                fieldNames: ['password', 'salt']
            )
        );
        $queries->addQuery(
            new SetEmailAvailableInTemplateQuery(
                entityClass: Audit::class,
                availableInTemplate: false,
                immutable: true
            )
        );
    }
}
