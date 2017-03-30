<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Oro\Bundle\WorkflowBundle\Form\Handler\TransitionFormHandlerInterface;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Helper\TransitionWidgetHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class WorkflowController extends Controller
{
    /**
     * @Route(
     *      "/workflow/start/{workflowName}/{transitionName}",
     *      name="oro_frontend_workflow_start_transition_form"
     * )
     *
     * @Layout
     *
     * @param string $workflowName
     * @param string $transitionName
     * @param Request $request
     *
     * @return Response
     */
    public function startTransitionAction($workflowName, $transitionName, Request $request)
    {
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
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow($workflowItem);

        $transition = $workflow->getTransitionManager()->extractTransition($transitionName);
        $transitionForm = $this->get('oro_workflow.layout.data_provider.transition_form')
            ->getTransitionForm($transitionName, $workflowItem);

        $transitionForm->addError(new FormError('XXX ZZZ'));

        //$saved = $this->getTransitionFormHandler($transition)
        //    ->processTransitionForm($transitionForm, $workflowItem, $transition, $request);



        //if ($saved) {
        //    $redirectUrl = '/';
        //
        //    return new JsonResponse(['redirectUrl' => $redirectUrl]);
        //    if ($request->isXmlHttpRequest()) {
        //        return new JsonResponse(['redirectUrl' => $redirectUrl]);
        //    } else {
        //        return $this->redirect($redirectUrl);
        //    }
        //}

        $routeProvider = $this->container->get('oro_workflow.provider.transition_route');
        $routeParams = [
            'transitionName' => $transition->getName(),
            'workflowItemId' => $workflowItem->getId(),
        ];

        return [
            'workflowName' => $workflowItem->getWorkflowName(),
            'transitionName' => $transition->getName(),
            'data' => [
                'transition' => $transition,
                'formView' => $transitionForm->createView(),
                'formAction' => $this->generateUrl(
                    $routeProvider->getFormPageRoute(),
                    $routeParams
                ),
            ]
        ];
    }

    /**
     * @return TransitionWidgetHelper
     */
    protected function getTransitionWidgetHelper()
    {
        return $this->get('oro_workflow.helper.transition_widget');
    }

    /**
     * @param Transition $transition
     *
     * @return TransitionFormHandlerInterface|object
     */
    protected function getTransitionFormHandler(Transition $transition)
    {
        $handlerName = 'oro_workflow.handler.transition.form';
        if ($transition->hasFormConfiguration()) {
            $handlerName = 'oro_workflow.handler.transition.form.page_form';
        }

        return $this->get($handlerName);
    }
}
