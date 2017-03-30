<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Form\Handler\TransitionFormHandlerInterface;
use Oro\Bundle\WorkflowBundle\Helper\TransitionWidgetHelper;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

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
     * @return Response
     */
    public function startTransitionFormAction($transitionName, $workflowName, Request $request)
    {
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
                    $responseCode = 404;
                    $responseMessage = $e->getMessage();
                } catch (InvalidTransitionException $e) {
                    $responseCode = 400;
                    $responseMessage = $e->getMessage();
                } catch (ForbiddenTransitionException $e) {
                    $responseCode = 403;
                    $responseMessage = $e->getMessage();
                } catch (\Exception $e) {
                    $responseCode = 500;
                    $responseMessage = $e->getMessage();
                }

                if (!isset($e)) {
                    //if ($request->isXmlHttpRequest()) {
                    //    return new JsonResponse(['redirectUrl' => '/']);
                    //} else {
                    return $this->redirect('/');
                    //}
                }
            }
        }

        return [
            'data' => [
                'transition' => $transition,
                'workflowItem' => $workflowItem,
                'formRouteName' => 'oro_frontend_workflow_widget_transition_form',
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
