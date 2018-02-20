<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class CustomerRolePageListener
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     */
    public function __construct(TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    /**
     * Adds rendered Workflows ACL datagrid block on edit role page.
     *
     * @param BeforeFormRenderEvent $event
     */
    public function onUpdatePageRender(BeforeFormRenderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $route = $request->attributes->get('_route');

        if (!in_array(
            $route,
            ['oro_customer_customer_user_role_update', 'oro_customer_customer_user_role_create'],
            true
        )) {
            // not a manipulate role page
            return;
        }

        $event->setFormData(
            $this->addWorkflowAclDatagrid(
                $event->getFormData(),
                $event->getTwigEnvironment(),
                $event->getForm()->vars['value'],
                false
            )
        );
    }

    /**
     * Adds rendered readonly Workflows ACL datagrid block on edit role page.
     *
     * @param BeforeViewRenderEvent $event
     */
    public function onViewPageRender(BeforeViewRenderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        if ($request->attributes->get('_route') !== 'oro_customer_customer_user_role_view') {
            // we are not at view role page
            return;
        }

        $event->setData(
            $this->addWorkflowAclDatagrid(
                $event->getData(),
                $event->getTwigEnvironment(),
                $event->getEntity(),
                true
            )
        );
    }

    /**
     * Adds the Workflow ACL datagrid block to the page data and return updated data array.
     *
     * @param array             $pageData
     * @param \Twig_Environment $twigEnvironment
     * @param CustomerUserRole  $entity
     * @param boolean           $readOnly
     *
     * @return array
     */
    protected function addWorkflowAclDatagrid(
        $pageData,
        \Twig_Environment $twigEnvironment,
        CustomerUserRole $entity,
        $readOnly
    ) {
        $dataBlocks = $pageData['dataBlocks'];
        $resultBlocks = [];
        foreach ($dataBlocks as $id => $dataBlock) {
            $resultBlocks[] = $dataBlock;
            // insert Workflow ACL Grid block after the entity block
            if ($id === 1) {
                $resultBlocks[] = [
                    'title'     => $this->translator->trans('oro.workflow.workflowdefinition.entity_plural_label'),
                    'subblocks' => [
                        [
                            'data' => [
                                $this->getRenderedGridHtml($twigEnvironment, $entity, $readOnly)
                            ]
                        ]
                    ]
                ];
            }
        }

        $pageData['dataBlocks'] = $resultBlocks;

        return $pageData;
    }

    /**
     * Renders Datagrid html for given role
     *
     * @param \Twig_Environment $twigEnvironment
     * @param CustomerUserRole  $entity
     * @param boolean           $readOnly
     *
     * @return string
     */
    protected function getRenderedGridHtml(\Twig_Environment $twigEnvironment, CustomerUserRole $entity, $readOnly)
    {
        return $twigEnvironment->render(
            'OroCustomerBundle:CustomerUserRole:aclGrid.html.twig',
            [
                'entity'     => $entity,
                'isReadonly' => $readOnly
            ]
        );
    }
}
