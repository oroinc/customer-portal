<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Processor\Context\LayoutPageResultType;
use Oro\Bundle\WorkflowBundle\Processor\Context\TransitionContext;
use Oro\Bundle\WorkflowBundle\Processor\TransitActionProcessor;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Frontend workflow controller
 */
class WorkflowController extends AbstractController
{
    /**
     *
     *
     * @param string $workflowName
     * @param string $transitionName
     * @param Request $request
     * @return array|Response
     */
    #[Route(
        path: '/workflow/start/{workflowName}/{transitionName}',
        name: 'oro_frontend_workflow_start_transition_form'
    )]
    #[Layout(vars: ['transitionName', 'workflowName'])]
    public function startTransitionAction($workflowName, $transitionName, Request $request)
    {
        $processor = $this->container->get(TransitActionProcessor::class);

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
     *
     *
     * @param string $transitionName
     * @param WorkflowItem $workflowItem
     * @param Request $request
     * @return array|Response
     */
    #[Route(
        path: '/workflow/transit/{workflowItemId}/{transitionName}',
        name: 'oro_frontend_workflow_transition_form'
    )]
    #[Layout(vars: ['transitionName', 'workflowName'])]
    public function transitionAction(
        $transitionName,
        #[MapEntity(id: 'workflowItemId')]
        WorkflowItem $workflowItem,
        Request $request
    ) {
        $processor = $this->container->get(TransitActionProcessor::class);

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

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            TransitActionProcessor::class,
        ]);
    }
}
