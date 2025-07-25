<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Command;

use Oro\Bundle\NavigationBundle\Command\JsRoutingDumpCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Dumps exposed storefront routes into a file.
 */
#[AsCommand(
    name: 'oro:frontend:js-routing:dump',
    description: 'Dumps exposed storefront routes into a file.',
    hidden: true
)]
class FrontendJsRoutingDumpCommand extends JsRoutingDumpCommand
{
    private const FRONTEND_FILENAME_PREFIX = 'frontend_';

    #[\Override]
    protected function configure()
    {
        parent::configure();
        $this->getDefinition()->getOption('target')->setDefault(
            $this->fileManager->getFilePath(self::FRONTEND_FILENAME_PREFIX . 'routes.json')
        );
    }

    #[\Override]
    protected function normalizeTargetPath(string $targetPath): string
    {
        $targetPath = parent::normalizeTargetPath($targetPath);
        $pos = strrpos($targetPath, DIRECTORY_SEPARATOR);
        if (false === $pos && DIRECTORY_SEPARATOR !== '/') {
            $pos = strrpos($targetPath, '/');
        }

        if (false === $pos) {
            return $this->getFrontendFileName($targetPath);
        }

        return substr($targetPath, 0, $pos + 1) . $this->getFrontendFileName(substr($targetPath, $pos + 1));
    }

    private function getFrontendFileName(string $fileName): string
    {
        $result = $fileName;
        if (str_starts_with($result, $this->filenamePrefix)) {
            return self::FRONTEND_FILENAME_PREFIX . substr($result, \strlen($this->filenamePrefix));
        }
        if (!str_starts_with($result, self::FRONTEND_FILENAME_PREFIX)) {
            $result = self::FRONTEND_FILENAME_PREFIX . $result;
        }

        return $result;
    }
}
