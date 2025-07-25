<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\EventListener\CustomerRolePageListener;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CustomerRolePageListenerTest extends TestCase
{
    private CustomerRolePageListener $listener;
    private RequestStack $requestStack;

    #[\Override]
    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($value) {
                return 'translated: ' . $value;
            });

        $this->requestStack = new RequestStack();
        $this->listener = new CustomerRolePageListener($translator, $this->requestStack);
    }

    public function testOnUpdatePageRenderWithoutRequest(): void
    {
        $event = new BeforeFormRenderEvent(
            $this->createMock(FormView::class),
            [],
            $this->createMock(Environment::class),
            null
        );

        $this->listener->onUpdatePageRender($event);

        $this->assertEquals([], $event->getFormData());
    }

    public function testOnUpdatePageRenderOnWrongPage(): void
    {
        $event = new BeforeFormRenderEvent(
            $this->createMock(FormView::class),
            [],
            $this->createMock(Environment::class),
            null
        );

        $this->requestStack->push(new Request([], [], ['_route' => 'some_route']));

        $this->listener->onUpdatePageRender($event);

        $this->assertEquals([], $event->getFormData());
    }

    public function testOnUpdatePageRenderOnNonCloneRolePage(): void
    {
        $event = new BeforeFormRenderEvent(
            $this->createMock(FormView::class),
            [],
            $this->createMock(Environment::class),
            null
        );

        $this->requestStack->push(
            new Request(
                [],
                [],
                ['_route' => 'oro_action_widget_form', '_route_params' => ['operationName' => 'some_operation']]
            )
        );

        $this->listener->onUpdatePageRender($event);

        $this->assertEquals([], $event->getFormData());
    }

    /**
     * @dataProvider onUpdatePageRenderRoutesProvider
     */
    public function testOnUpdatePageRender(string $routeName): void
    {
        $entity = new CustomerUserRole('');
        $form = new FormView();
        $form->vars['value'] = $entity;
        $twig = $this->createMock(Environment::class);
        $event = new BeforeFormRenderEvent(
            $form,
            [
                'dataBlocks' => [
                    ['first block'],
                    ['second block'],
                    ['third block']
                ]
            ],
            $twig,
            null
        );

        $renderedHtml = '<div>Rendered datagrid position</div>';
        $twig->expects($this->once())
            ->method('render')
            ->with(
                '@OroCustomer/CustomerUserRole/aclGrid.html.twig',
                [
                    'entity'     => $entity,
                    'isReadonly' => false
                ]
            )
            ->willReturn($renderedHtml);

        $this->requestStack->push(new Request([], [], ['_route' => $routeName]));

        $this->listener->onUpdatePageRender($event);

        $data = $event->getFormData();
        $this->assertCount(4, $data['dataBlocks']);
        $workflowBlock = $data['dataBlocks'][2];
        $this->assertEquals(
            'translated: oro.workflow.workflowdefinition.entity_plural_label',
            $workflowBlock['title']
        );
        $this->assertEquals(
            [['data' => [$renderedHtml]]],
            $workflowBlock['subblocks']
        );
    }

    public function onUpdatePageRenderRoutesProvider(): array
    {
        return [
            ['oro_customer_customer_user_role_update'],
            ['oro_customer_customer_user_role_create'],
        ];
    }

    public function testOnViewPageRenderWithoutRequest(): void
    {
        $event = new BeforeViewRenderEvent(
            $this->createMock(Environment::class),
            [],
            new \stdClass()
        );

        $this->listener->onViewPageRender($event);

        $this->assertEquals([], $event->getData());
    }

    public function testOnViewPageRenderOnNonUpdateRolePage(): void
    {
        $event = new BeforeViewRenderEvent(
            $this->createMock(Environment::class),
            [],
            new \stdClass()
        );

        $this->requestStack->push(new Request([], [], ['_route' => 'some_route']));

        $this->listener->onViewPageRender($event);

        $this->assertEquals([], $event->getData());
    }

    public function testOnViewPageRender(): void
    {
        $entity = new CustomerUserRole('');
        $twig = $this->createMock(Environment::class);
        $event = new BeforeViewRenderEvent(
            $twig,
            [
                'dataBlocks' => [
                    ['first block'],
                    ['second block'],
                    ['third block']
                ]
            ],
            $entity
        );

        $renderedHtml = '<div>Rendered datagrid position</div>';
        $twig->expects($this->once())
            ->method('render')
            ->with(
                '@OroCustomer/CustomerUserRole/aclGrid.html.twig',
                [
                    'entity'     => $entity,
                    'isReadonly' => true
                ]
            )
            ->willReturn($renderedHtml);

        $this->requestStack->push(new Request([], [], ['_route' => 'oro_customer_customer_user_role_view']));

        $this->listener->onViewPageRender($event);

        $data = $event->getData();
        $this->assertCount(4, $data['dataBlocks']);
        $workflowBlock = $data['dataBlocks'][2];
        $this->assertEquals(
            'translated: oro.workflow.workflowdefinition.entity_plural_label',
            $workflowBlock['title']
        );
        $this->assertEquals(
            [['data' => [$renderedHtml]]],
            $workflowBlock['subblocks']
        );
    }
}
