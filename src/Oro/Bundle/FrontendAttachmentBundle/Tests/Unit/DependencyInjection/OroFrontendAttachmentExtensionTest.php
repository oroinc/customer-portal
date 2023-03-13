<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\FrontendAttachmentBundle\DependencyInjection\OroFrontendAttachmentExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroFrontendAttachmentExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroFrontendAttachmentExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
    }
}
