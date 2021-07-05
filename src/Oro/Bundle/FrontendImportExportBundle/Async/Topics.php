<?php

namespace Oro\Bundle\FrontendImportExportBundle\Async;

/**
 * Topics storage for import and export processors.
 */
class Topics
{
    public const PRE_EXPORT = 'oro_frontend_importexport.pre_export';
    public const EXPORT = 'oro_frontend_importexport.export';
    public const POST_EXPORT = 'oro_frontend_importexport.post_export';
    public const SAVE_EXPORT_RESULT = 'oro_frontend_importexport.save_import_export_result';
}
