<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Symfony\Component\Form\FormView;

use Twig_Environment;

use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\CommerceMenuBundle\EventListener\MenuUpdateFormViewListener;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;

class MenuUpdateFormViewListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnEdit()
    {
        $menuUpdate = new MenuUpdate;
        $formView = new FormView();
        $formView->vars['value'] = $menuUpdate;

        $environment = $this->createMock(Twig_Environment::class);
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

        $event = new BeforeListRenderEvent($environment, $scrollData, $formView);


        $listener = new MenuUpdateFormViewListener;

        $listener->onEdit($event);

        $this->assertEquals(
            [$template],
            $event->getScrollData()->getData()['dataBlocks']['block_1']['subblocks']['subblock_1']['data']
        );
    }
}
