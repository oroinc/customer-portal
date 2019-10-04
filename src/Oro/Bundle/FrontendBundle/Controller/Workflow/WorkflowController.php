<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Processor\Context\LayoutPageResultType;
use Oro\Bundle\WorkflowBundle\Processor\Context\TransitionContext;
use Oro\Bundle\WorkflowBundle\Processor\TransitActionProcessor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Frontend workflow controller
 */
class WorkflowController extends AbstractController
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
        $processor = $this->get(TransitActionProcessor::class);

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
        $processor = $this->get(TransitActionProcessor::class);

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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            TransitActionProcessor::class,
        ]);
    }
}
