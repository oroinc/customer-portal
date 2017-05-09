<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Processor\Context\LayoutPageResultType;
use Oro\Bundle\WorkflowBundle\Processor\Context\TransitionContext;
use Oro\Bundle\WorkflowBundle\Processor\TransitActionProcessor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkflowController extends Controller
{
    /**
     * @Route(
     *      "/workflow/start/{workflowName}/{transitionName}",
     *      name="oro_frontend_workflow_start_transition_form"
     * )
     *
     * @Layout(vars={"transitionName", "workflowName"})
     *
     * @param string $workflowName
     * @param string $transitionName
     * @param Request $request
     *
     * @return array|Response
     */
    public function startTransitionAction($workflowName, $transitionName, Request $request)
    {
        $processor = $this->getProcessor();

        $context = $this->createProcessorContext(
            $processor,
            $request,
            'oro_frontend_workflow_start_transition_form',
            $transitionName
        );
        $context->setWorkflowName($workflowName);

        $processor->process($context);

        return $context->getResult();
    }

    /**
     * @Route(
     *      "/workflow/transit/{workflowItemId}/{transitionName}",
     *      name="oro_frontend_workflow_transition_form"
     * )
     * @ParamConverter("workflowItem", options={"id"="workflowItemId"})
     *
     * @Layout(vars={"transitionName", "workflowName"})
     *
     * @param string $transitionName
     * @param WorkflowItem $workflowItem
     * @param Request $request
     *
     * @return array|Response
     */
    public function transitionAction($transitionName, WorkflowItem $workflowItem, Request $request)
    {
        $processor = $this->getProcessor();

        $context = $this->createProcessorContext(
            $processor,
            $request,
            'oro_frontend_workflow_transition_form',
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
        $context->setResultType(new LayoutPageResultType($formRouteName));

        return $context;
    }
}
