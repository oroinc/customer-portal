<?php

namespace Oro\Bundle\FrontendBundle\Command;

use FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractorInterface;
use Oro\Bundle\UIBundle\Command\JsRoutingDumpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dumps JS routes for frontend application
 */
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

        $webRootDir = $this->getContainer()->getParameter('kernel.project_dir');
        if ($webRootDir) {
            $input->setOption('target', $webRootDir . '/public/js/frontend_routes.js');
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
