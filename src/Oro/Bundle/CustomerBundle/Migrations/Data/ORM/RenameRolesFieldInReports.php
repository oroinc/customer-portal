<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ReportBundle\Entity\Report;
use Oro\Bundle\SegmentBundle\Migration\AbstractRenameField;

/**
 * Renames roles field to userRoles in reports for CustomerUser entity.
 */
class RenameRolesFieldInReports extends AbstractRenameField
{
    /**
     * {@inheritdoc}
     */
    protected function getOldFieldName(): string
    {
        return 'roles';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNewFieldName(): string
    {
        return 'userRoles';
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryAwareEntities(ObjectManager $manager): array
    {
        return $manager->getRepository(Report::class)->findBy(['entity' => CustomerUser::class]);
    }
}
