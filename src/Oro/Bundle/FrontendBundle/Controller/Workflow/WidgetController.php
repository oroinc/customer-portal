<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Processor\Context\LayoutDialogResultType;
use Oro\Bundle\WorkflowBundle\Processor\Context\TransitionContext;
use Oro\Bundle\WorkflowBundle\Processor\TransitActionProcessor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WidgetController extends Controller
{
    /**
     * @Route(
     *      "/workflow/widget/start/{workflowName}/{transitionName}",
     *      name="oro_frontend_workflow_widget_start_transition_form"
     * )
     *
     * @Layout
     *
     * @param string $transitionName
     * @param string $workflowName
     * @param Request $request
     *
     * @return Response|array
     */
    public function startTransitionFormAction($transitionName, $workflowName, Request $request)
    {
        $processor = $this->getProcessor();

        $context = $this->createProcessorContext(
            $processor,
            $request,
            'oro_frontend_workflow_widget_start_transition_form',
            $transitionName
        );
        $context->setWorkflowName($workflowName);

        $processor->process($context);

        return $context->getResult();
    }

    /**
     * @Route(
     *      "/workflow/widget/transit/{workflowItemId}/{transitionName}",
     *      name="oro_frontend_workflow_widget_transition_form"
     * )
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     *
     * @Layout
     *
     * @param string $transitionName
     * @param WorkflowItem $workflowItem
     * @param Request $request
     *
     * @return Response|array
     */
    public function transitionFormAction($transitionName, WorkflowItem $workflowItem, Request $request)
    {
        $processor = $this->getProcessor();

        $context = $this->createProcessorContext(
            $processor,
            $request,
            'oro_frontend_workflow_widget_transition_form',
            $transitionName
        );
        $context->setWorkflowItem($workflowItem);

        $processor->process($context);

        return $context->getResult();
    }

    /**
     * @return TransitActionProcessor
     */
    private function getProcessor()
    {
        return $this->get('oro_workflow.transit.action_processor');
    }

    /**
     * @param TransitActionProcessor $processor
     * @param Request $request
     * @param string $formRouteName
     * @param string $transitionName
     * @return TransitionContext
     */
    private function createProcessorContext(
        TransitActionProcessor $processor,
        Request $request,
        string $formRouteName,
        string $transitionName
    ) {
        /** @var TransitionContext $context */
        $context = $processor->createContext();
        $context->setTransitionName($transitionName);
        $context->setRequest($request);
        $context->setResultType(new LayoutDialogResultType($formRouteName));

        return $context;
    }
}
