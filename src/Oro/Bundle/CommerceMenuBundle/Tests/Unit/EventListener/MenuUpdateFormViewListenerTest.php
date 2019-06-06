<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\EventListener\MenuUpdateFormViewListener;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Symfony\Component\Form\FormView;
use Twig\Environment;

class MenuUpdateFormViewListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testOnEdit()
    {
        $menuUpdate = new MenuUpdate;
        $formView = new FormView();
        $formView->vars['value'] = $menuUpdate;

        $environment = $this->createMock(Environment::class);
        $template = 'rendered_template_content';
        $environment->expects($this->once())
            ->method('render')
            ->with('OroCommerceMenuBundle:menuUpdate:commerce_menu_update_fields.html.twig', ['form' => $formView])
            ->willReturn($template);
        $scrollData = new ScrollData(
            [
                'dataBlocks' => [
                    'block_1' => [
                        'subblocks' => [
                            'subblock_1' => ['data' => []],
                            'subblock_2' => ['data' => []],
                        ]
                    ],
                    'block_2' => ['subblocks' => []]
                ]
            ]
        );

        $event = new BeforeListRenderEvent($environment, $scrollData, new \stdClass(), $formView);

        $listener = new MenuUpdateFormViewListener;

        $listener->onEdit($event);

        $this->assertEquals(
            [$template],
            $event->getScrollData()->getData()['dataBlocks']['block_1']['subblocks']['subblock_1']['data']
        );
    }
}
