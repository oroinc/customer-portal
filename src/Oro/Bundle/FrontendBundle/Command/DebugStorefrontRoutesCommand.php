<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Displays storefront routes list
 */
#[AsCommand(
    name: 'oro:debug:storefront-routes',
    description: 'Lists storefront routes'
)]
class DebugStorefrontRoutesCommand extends Command
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
        parent::__construct();
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    #[\Override]
    public function configure()
    {
        $this
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command list of routes used in the storefront

  <info>php %command.full_name%</info>
HELP
            );
    }

    #[\Override]
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $routes = $this->router->getRouteCollection();
        $storeFrontRoutes = [];
        foreach ($routes as $routeName => $route) {
            if ($route->hasOption('frontend') && $route->getOption('frontend')) {
                $storeFrontRoutes[] = $routeName;
            }
        }

        $output->write(Yaml::dump($storeFrontRoutes, 8));

        return static::SUCCESS;
    }
}
