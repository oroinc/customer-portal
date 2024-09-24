<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\AttachmentBundle\Provider\FilesTemplateProvider as BaseFilesTemplateProvider;
use Oro\Bundle\FrontendBundle\Provider\FilesTemplateProvider;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilesTemplateProviderTest extends TestCase
{
    private FrontendHelper|MockObject $frontendHelper;

    private FilesTemplateProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->provider = new FilesTemplateProvider(
            new BaseFilesTemplateProvider(),
            $this->frontendHelper
        );
    }

    public function testGetTemplateBackend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        self::assertEquals('@OroAttachment/Twig/file.html.twig', $this->provider->getTemplate());
    }

    public function testGetTemplateStorefront(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        self::assertEquals('@OroFrontend/Twig/file.html.twig', $this->provider->getTemplate());
    }

    public function testSetTemplateBackend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->provider->setTemplate('@ACMEAttachment/Twig/file.html.twig');

        self::assertEquals('@OroAttachment/Twig/file.html.twig', $this->provider->getTemplate());
    }

    public function testSetTemplateStorefront(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $template = '@ACMEAttachment/Twig/file.html.twig';

        $this->provider->setTemplate($template);

        self::assertEquals($template, $this->provider->getTemplate());
    }
}
