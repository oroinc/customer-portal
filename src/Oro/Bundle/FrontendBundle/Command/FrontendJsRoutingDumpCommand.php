<?php

namespace Oro\Bundle\FrontendBundle\Command;

use FOS\JsRoutingBundle\Command\DumpCommand;
use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Dumps JS routes for frontend application
 */
class FrontendJsRoutingDumpCommand extends DumpCommand
{
    /** @var string */
    protected static $defaultName = 'oro:frontend:js-routing:dump';

    /** @var string */
    private $projectDir;

    /** @var string */
    private $backendFilenamePrefix;

    /**
     * @param ExposedRoutesExtractorInterface $extractor
     * @param SerializerInterface $serializer
     * @param string $projectDir
     * @param string|null $requestContextBaseUrl
     * @param string $backendFilenamePrefix
     */
    public function __construct(
        ExposedRoutesExtractorInterface $extractor,
        SerializerInterface $serializer,
        $projectDir,
        $requestContextBaseUrl = null,
        string $backendFilenamePrefix = ''
    ) {
        parent::__construct($extractor, $serializer, $projectDir, $requestContextBaseUrl);

        $this->projectDir = $projectDir;
        $this->backendFilenamePrefix = $backendFilenamePrefix;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setHidden(true);

        $definition = $this->getDefinition();
        $definition->getOption('format')
            ->setDefault('json');
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param string $backendFilename
     * @return string
     */
    private function getFilename(string $backendFilename): string
    {
        $filename = ltrim(str_replace($this->backendFilenamePrefix, '', $backendFilename), '_');

        return sprintf('frontend_%s', $filename);
    }
}
