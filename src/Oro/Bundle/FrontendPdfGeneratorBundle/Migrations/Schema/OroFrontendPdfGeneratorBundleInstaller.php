<?php

namespace Oro\Bundle\FrontendPdfGeneratorBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProviderInterface;
use Oro\Bundle\AttachmentBundle\Migration\UpdateEntityFieldAttachmentConfigQuery;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigMigrationQuery;
use Oro\Bundle\EntityConfigBundle\Tools\CommandExecutor;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\RefreshExtendCacheMigrationQuery;
use Oro\Bundle\EntityExtendBundle\Migration\RefreshExtendConfigMigrationQuery;
use Oro\Bundle\EntityExtendBundle\Migration\UpdateExtendConfigMigrationQuery;
use Oro\Bundle\FrontendBundle\Provider\FrontendCurrentApplicationProvider;
use Oro\Bundle\MigrationBundle\Migration\Extension\DataStorageExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\DataStorageExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\PdfGeneratorBundle\Entity\PdfDocument;
use Oro\Bundle\SecurityBundle\Migrations\Schema\UpdateSecurityConfigQuery;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class OroFrontendPdfGeneratorBundleInstaller implements
    Installation,
    ContainerAwareInterface,
    ExtendExtensionAwareInterface,
    DataStorageExtensionAwareInterface
{
    use ContainerAwareTrait;
    use ExtendExtensionAwareTrait;

    private DataStorageExtension $dataStorageExtension;

    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v6_1_3_0';
    }

    #[\Override]
    public function setDataStorageExtension(DataStorageExtension $dataStorageExtension): void
    {
        $this->dataStorageExtension = $dataStorageExtension;
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        if (!$schema->hasTable('oro_pdf_generator_pdf_document')) {
            return;
        }

        // Refreshes entity config to make the further migration queries work as they change
        // the PdfDocument entity config, that does not yet exist as PdfDocument entity is created in PdfGeneratorBundle
        // within the same installation process.
        $this->refreshEntityConfig($queries, $schema);

        $this->updatePdfDocumentSecurityGroup($queries);
        $this->updatePdfDocumentFileFieldConfig($queries);
    }

    private function refreshEntityConfig(QueryBag $queries, Schema $schema): void
    {
        /** @var CommandExecutor $commandExecutor */
        $commandExecutor = $this->container->get('oro_entity_config.tools.command_executor');

        /** @see \Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigMigration */
        $queries->addPreQuery(new UpdateEntityConfigMigrationQuery($commandExecutor));

        /** @see \Oro\Bundle\EntityExtendBundle\Migration\UpdateExtendConfigMigration */
        $queries->addPreQuery(
            new UpdateExtendConfigMigrationQuery(
                $schema->getExtendOptions(),
                $commandExecutor,
                $this->container->getParameter('oro_entity_extend.migration.config_processor.options.path'),
            )
        );
        $queries->addPreQuery(
            new RefreshExtendConfigMigrationQuery(
                $commandExecutor,
                $this->dataStorageExtension->get('initial_entity_config_state', []),
                $this->container->getParameter('oro_entity_extend.migration.initial_entity_config_state.path'),
            )
        );
        $queries->addQuery(
            new RefreshExtendCacheMigrationQuery(
                $commandExecutor
            )
        );
    }

    private function updatePdfDocumentSecurityGroup(QueryBag $queries): void
    {
        $securityQuery = new UpdateSecurityConfigQuery(
            PdfDocument::class,
            [
                'group_name' => 'commerce',
            ]
        );
        $queries->addPostQuery($securityQuery);
    }

    private function updatePdfDocumentFileFieldConfig(QueryBag $queries): void
    {
        $attachmentQuery = new UpdateEntityFieldAttachmentConfigQuery(
            PdfDocument::class,
            'pdfDocumentFile',
            [
                'file_applications' => [
                    CurrentApplicationProviderInterface::DEFAULT_APPLICATION,
                    FrontendCurrentApplicationProvider::COMMERCE_APPLICATION,
                ],
            ]
        );
        $queries->addPostQuery($attachmentQuery);
    }
}
