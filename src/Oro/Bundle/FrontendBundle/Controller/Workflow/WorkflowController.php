<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Helper\TransitionWidgetHelper;
use Oro\Bundle\WorkflowBundle\Form\Handler\TransitionFormHandlerInterface;
use Oro\Bundle\WorkflowBundle\Model\Transition;
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
     * @return array|Response
     */
    public function startTransitionAction($workflowName, $transitionName, Request $request)
    {
        return [
            'data' => [
                'workflowName' => $workflowName,
                'transitionName' => $transitionName,
                'formRouteName' => 'oro_frontend_workflow_start_transition_form',
            ]
        ];
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

        if ($request->isMethod('POST')) {
            $saved = $this->getTransitionFormHandler($transition)
                ->processTransitionForm($transitionForm, $workflowItem, $transition, $request);
            if ($saved) {
                try {
                    $workflowManager->transit($workflowItem, $transition);
                } catch (WorkflowNotFoundException $e) {
                } catch (InvalidTransitionException $e) {
                } catch (ForbiddenTransitionException $e) {
                } catch (\Exception $e) {
                }

                if (!isset($e)) {
                    $url = '/';
                    if ($workflowItem->getResult()->get('redirectUrl')) {
                        $url = $workflowItem->getResult()->get('redirectUrl');
                    } else {
                        if ($request->headers->get('referer')) {
                            $url = $request->headers->get('referer');
                        }
                    }

                    return $this->redirect($url);
                }
            }
        }

        return [
            'workflowName' => $workflowItem->getWorkflowName(),
            'transitionName' => $transition->getName(),
            'data' => [
                'transition' => $transition,
                'workflowItem' => $workflowItem,
                'formRouteName' => 'oro_frontend_workflow_transition_form',
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
