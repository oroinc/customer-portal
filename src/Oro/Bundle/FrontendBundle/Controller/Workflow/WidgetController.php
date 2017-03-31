<?php

namespace Oro\Bundle\FrontendBundle\Controller\Workflow;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\WorkflowBundle\Configuration\FeatureConfigurationExtension;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Form\Handler\TransitionFormHandlerInterface;
use Oro\Bundle\WorkflowBundle\Helper\TransitionWidgetHelper;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
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
     * @return Response|array
     */
    public function startTransitionFormAction($transitionName, $workflowName, Request $request)
    {
        $entityId = $request->get('entityId', 0);
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow($workflowName);
        $entityClass = $workflow->getDefinition()->getRelatedEntity();
        $transition = $workflow->getTransitionManager()->extractTransition($transitionName);
        $dataArray = [];

        if (!$transition->isEmptyInitOptions()) {
            $contextAttribute = $transition->getInitContextAttribute();
            $dataArray[$contextAttribute] = $this->get('oro_action.provider.button_search_context')
                ->getButtonSearchContext();
            $entityId = null;
        }

        $entity = $this->getTransitionWidgetHelper()->getOrCreateEntityReference($entityClass, $entityId);
        $workflowItem = $workflow->createWorkflowItem($entity, $dataArray);

        $transitionForm = $this->get('oro_workflow.layout.data_provider.transition_form')
            ->getTransitionForm($transitionName, $workflowItem);

        if ($request->isMethod('POST')) {
            $saved = $this->getTransitionFormHandler($transition)
                ->processStartTransitionForm($transitionForm, $workflowItem, $transition, $request);
            if ($saved) {
                $data = $this->getTransitionWidgetHelper()
                    ->processWorkflowData($workflow, $transition, $transitionForm, $dataArray);

                try {
                    if (!$this->get('oro_featuretoggle.checker.feature_checker')
                        ->isResourceEnabled($workflowName, FeatureConfigurationExtension::WORKFLOWS_NODE_NAME)
                    ) {
                        throw new ForbiddenTransitionException();
                    }
                    $dataArray = [];
                    if ($data) {
                        $serializer = $this->get('oro_workflow.serializer.data.serializer');
                        $serializer->setWorkflowName($workflowName);
                        /* @var $data WorkflowData */
                        $data = $serializer->deserialize(
                            $data,
                            'Oro\Bundle\WorkflowBundle\Model\WorkflowData',
                            'json'
                        );
                        $dataArray = $data->getValues();
                    }

                    $workflowItem = $workflowManager->startWorkflow($workflowName, $entity, $transition, $dataArray);
                } catch (WorkflowNotFoundException $e) {
                } catch (InvalidTransitionException $e) {
                } catch (ForbiddenTransitionException $e) {
                } catch (\Exception $e) {
                }

                if (!isset($e)) {
                    $url = '/';
                    if ($workflowItem->getResult()->get('redirectUrl')) {
                        $url = $workflowItem->getResult()->get('redirectUrl');
                    } elseif ($request->headers->get('referer')) {
                        $url = $request->headers->get('referer');
                    }

                    if ($request->isXmlHttpRequest()) {
                        return JsonResponse::create([
                            'workflowItem' => ['result' => ['redirectUrl' => $url]]
                        ]);
                    } else {
                        return $this->redirect($url);
                    }
                }
            }
        }

        return [
            'data' => [
                'workflowName' => $workflowName,
                'workflowItem' => $workflowItem,
                'transition' => $transition,
                'transitionName' => $transitionName,
                'entityId' => $request->get('entityId', 0),
                'formRouteName' => 'oro_frontend_workflow_widget_start_transition_form',
            ]
        ];
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
                } catch (InvalidTransitionException $e) {
                } catch (ForbiddenTransitionException $e) {
                } catch (\Exception $e) {
                }

                if (!isset($e)) {
                    $url = '/';
                    if ($workflowItem->getResult()->get('redirectUrl')) {
                        $url = $workflowItem->getResult()->get('redirectUrl');
                    } elseif ($request->headers->get('referer')) {
                        $url = $request->headers->get('referer');
                    }

                    if ($request->isXmlHttpRequest()) {
                        return JsonResponse::create([
                            'workflowItem' => ['result' => $workflowItem->getResult()->toArray()]
                        ]);
                    } else {
                        return $this->redirect($url);
                    }
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
