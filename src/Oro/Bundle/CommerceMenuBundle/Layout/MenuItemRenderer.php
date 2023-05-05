<?php

namespace Oro\Bundle\CommerceMenuBundle\Layout;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LayoutBundle\Layout\LayoutManager;
use Oro\Component\Layout\LayoutContext;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Renders a menu item via layout.
 */
class MenuItemRenderer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const ERROR_TEMPLATE = <<<HTML
<div class="alert alert-error alert--compact" role="alert">
    <span class="fa-exclamation alert-icon" aria-hidden="true"></span>
    Rendering of the menu item "%s" failed: %s
</div>
HTML;

    private LayoutManager $layoutManager;

    private bool $debug;

    public function __construct(LayoutManager $layoutManager, bool $debug)
    {
        $this->layoutManager = $layoutManager;
        $this->debug = $debug;

        $this->logger = new NullLogger();
    }

    public function render(ItemInterface $menuItem): string
    {
        try {
            $layoutFactory = $this->layoutManager->getLayoutFactory();
            $layoutBuilder = $layoutFactory->createLayoutBuilder();
            $layoutBuilder->add('menu_item_root', null, 'container');

            $layoutContext = new LayoutContext(
                [
                    'data' => ['menu_item' => $menuItem],
                    'menu_template' => (string)$menuItem->getExtra(MenuUpdate::MENU_TEMPLATE),
                    'menu_item_name' => $menuItem->getName(),
                ],
                ['menu_template', 'menu_item_name']
            );

            return $layoutBuilder->getLayout($layoutContext)->render();
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Error occurred while rendering menu item "{menu_item_name}": {error}',
                [
                    'throwable' => $throwable,
                    'error' => $throwable->getMessage(),
                    'menu_item_name' => $menuItem->getName(),
                    'menu_item' => $menuItem,
                ]
            );

            if ($this->debug) {
                return sprintf(self::ERROR_TEMPLATE, $menuItem->getName(), $throwable->getMessage());
            }
        }

        return '';
    }
}
