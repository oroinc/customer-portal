<?php

namespace Oro\Bundle\FrontendBundle\Command;

use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Oro\Bundle\UIBundle\Command\JsRoutingDumpCommand;

use FOS\JsRoutingBundle\Command\DumpCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FrontendJsRoutingDumpCommand extends JsRoutingDumpCommand
{
    /**
     * @var ExposedRoutesExtractorInterface
     */
    protected $routesExtractor;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $webRootDir = $this->getContainer()->getParameter('assetic.read_from');
        if ($webRootDir) {
            $input->setOption('target', $webRootDir . '/js/frontend_routes.js');
        }
        $this->routesExtractor = $this->getContainer()->get('oro_frontend.extractor.frontend_exposed_routes_extractor');
        $this->initialize($input, $output);
        parent::execute($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExposedRoutesExtractor()
    {
        if ($this->routesExtractor === null) {
            $this->routesExtractor = $this->getContainer()->get('fos_js_routing.extractor');
        }

        return $this->routesExtractor;
    }
}
