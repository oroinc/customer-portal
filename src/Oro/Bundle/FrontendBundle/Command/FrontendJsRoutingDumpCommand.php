<?php
declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Command;

use FOS\JsRoutingBundle\Command\DumpCommand;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Dumps exposed storefront routes into a file.
 * @deprecated {@see NewFrontendJsRoutingDumpCommand} used instead.
 */
class FrontendJsRoutingDumpCommand extends DumpCommand
{
    /** @var string */
    protected static $defaultName = 'oro:frontend:js-routing:dump';

    private string $projectDir;
    private string $backendFilenamePrefix;

    public function __construct(
        ExposedRoutesExtractorInterface $extractor,
        SerializerInterface $serializer,
        string $projectDir,
        ?string $requestContextBaseUrl = null,
        string $backendFilenamePrefix = ''
    ) {
        parent::__construct($extractor, $serializer, $projectDir, $requestContextBaseUrl);

        $this->projectDir = $projectDir;
        $this->backendFilenamePrefix = $backendFilenamePrefix;
    }

    protected function configure()
    {
        parent::configure();

        $this->setHidden(true);
        $this->setDescription('Dumps exposed storefront routes into a file.');
        $this->getDefinition()->getOption('format')->setDefault('json');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getOption('target');
        if ($target) {
            $parts = explode(DIRECTORY_SEPARATOR, $target);
            $parts[] = $this->getFilename(array_pop($parts));

            $input->setOption('target', implode(DIRECTORY_SEPARATOR, $parts));
        } else {
            $input->setOption(
                'target',
                implode(
                    DIRECTORY_SEPARATOR,
                    [
                        $this->projectDir,
                        'public',
                        'media',
                        'js',
                        'frontend_routes.' . $input->getOption('format')
                    ]
                )
            );
        }

        return parent::execute($input, $output);
    }

    private function getFilename(string $backendFilename): string
    {
        $filename = ltrim(str_replace($this->backendFilenamePrefix, '', $backendFilename), '_');

        return sprintf('frontend_%s', $filename);
    }
}
