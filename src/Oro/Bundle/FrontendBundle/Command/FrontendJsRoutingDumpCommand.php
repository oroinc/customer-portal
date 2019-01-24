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
    public const NAME = 'oro:frontend:js-routing:dump';

    /** @var string */
    private $projectDir;

    /**
     * @param ExposedRoutesExtractorInterface $extractor
     * @param SerializerInterface $serializer
     * @param string $projectDir
     * @param string|null $requestContextBaseUrl
     */
    public function __construct(
        ExposedRoutesExtractorInterface $extractor,
        SerializerInterface $serializer,
        $projectDir,
        $requestContextBaseUrl = null
    ) {
        parent::__construct($extractor, $serializer, $projectDir, $requestContextBaseUrl);

        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName(self::NAME)
            ->setHidden(true);

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
            $parts[] = 'frontend_' . array_pop($parts);

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
}
