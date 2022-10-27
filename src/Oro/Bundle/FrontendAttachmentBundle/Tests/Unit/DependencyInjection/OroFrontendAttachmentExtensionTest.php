<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendAttachmentBundle\DependencyInjection\OroFrontendAttachmentExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroFrontendAttachmentExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroFrontendAttachmentExtension());

        $expectedServices = [
            'oro_frontend_attachment.form.extension.file_attachment_config_extension',
        ];
        $this->assertDefinitionsLoaded($expectedServices);
    }
}
