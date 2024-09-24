<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\AttachmentBundle\Provider\FilesTemplateProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * Provides a path to a template that can be used to render a file view html block.
 */
class FilesTemplateProvider implements FilesTemplateProviderInterface
{
    private string $filesTemplate = '@OroFrontend/Twig/file.html.twig';

    public function __construct(
        private FilesTemplateProviderInterface $innerFilesTemplateProvider,
        private FrontendHelper $frontendHelper
    ) {
    }

    public function setTemplate(string $filesTemplate): void
    {
        $this->filesTemplate = $filesTemplate;
    }

    #[\Override]
    public function getTemplate(): string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->filesTemplate
            : $this->innerFilesTemplateProvider->getTemplate();
    }
}
