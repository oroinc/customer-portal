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

    private LayoutManager $layoutManager;

    public function __construct(LayoutManager $layoutManager)
    {
        $this->layoutManager = $layoutManager;
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
                    'menu_template' => (string) $menuItem->getExtra(MenuUpdate::MENU_TEMPLATE),
                    'menu_name' => $menuItem->getName(),
                ],
                ['menu_template', 'menu_name']
            );

            return $layoutBuilder->getLayout($layoutContext)->render();
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Error occurred while rendering menu item "{menu_item_name}".',
                ['throwable' => $throwable, 'menu_item_name' => $menuItem->getName(), 'menu_item' => $menuItem]
            );

            return '';
        }
    }
}
