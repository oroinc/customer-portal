<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Command;

use Oro\Bundle\NavigationBundle\Command\JsRoutingDumpCommand;

/**
 * Dumps exposed storefront routes into a file.
 */
class FrontendJsRoutingDumpCommand extends JsRoutingDumpCommand
{
    /** @var string */
    protected static $defaultName = 'oro:frontend:js-routing:dump';

    private const FRONTEND_FILENAME_PREFIX = 'frontend_';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setHidden(true);
        $this->setDescription('Dumps exposed storefront routes into a file.');
        $this->getDefinition()->getOption('target')->setDefault(
            $this->fileManager->getFilePath(self::FRONTEND_FILENAME_PREFIX . 'routes.json')
        );
    }

    /**
     * {@inheritDoc}
     */
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
