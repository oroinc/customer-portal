<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v6_1_11_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\EmailBundle\Migration\SetEmailAvailableInTemplateQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Disables the password reset confirmation token, the new email verification code and the visitor session id
 * in email templates and marks them as immutable, so they cannot be re-enabled through the UI.
 */
class DisableFieldsInEmailTemplates implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addQuery(new SetEmailAvailableInTemplateQuery(
            entityClass: CustomerUser::class,
            availableInTemplate: false,
            fieldNames: ['confirmationToken', 'newEmailVerificationCode'],
            force: true,
            immutable: true
        ));
        $queries->addQuery(new SetEmailAvailableInTemplateQuery(
            entityClass: CustomerVisitor::class,
            availableInTemplate: false,
            fieldNames: ['sessionId'],
            force: true,
            immutable: true
        ));
    }
}
