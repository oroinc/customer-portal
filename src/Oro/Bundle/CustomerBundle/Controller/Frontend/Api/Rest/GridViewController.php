<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Controller\Api\Rest\GridViewController as BaseGridViewController;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller for grid views.
 */
class GridViewController extends BaseGridViewController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Create grid view",
     *      resource=true,
     * )
     */
    #[Acl(
        id: 'oro_customer_frontend_gridview_create',
        type: 'entity',
        class: GridView::class,
        permission: 'CREATE',
        groupName: 'commerce'
    )]
    #[\Override]
    public function postAction(Request $request)
    {
        return parent::postAction($request);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Update grid view",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     */
    #[Acl(
        id: 'oro_customer_frontend_gridview_update',
        type: 'entity',
        class: GridView::class,
        permission: 'EDIT',
        groupName: 'commerce'
    )]
    #[\Override]
    public function putAction(Request $request, $id)
    {
        return parent::putAction($request, $id);
    }

    /**
     * @param int $id
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Delete grid view",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     */
    #[Acl(
        id: 'oro_customer_frontend_gridview_delete',
        type: 'entity',
        class: GridView::class,
        permission: 'DELETE',
        groupName: 'commerce'
    )]
    #[\Override]
    public function deleteAction($id)
    {
        return parent::deleteAction($id);
    }

    /**
     * Set/unset grid view as default for current user.
     *
     * @param int  $id
     * @param bool $default
     * @param null $gridName
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Set or unset grid view as default for current user",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="string"},
     *          {"name"="default", "dataType"="boolean"},
     *          {"name"="gridName", "dataType"="string"}
     *      },
     *      defaults={"default"="false"}
     * )
     */
    #[Acl(
        id: 'oro_customer_frontend_gridview_view',
        type: 'entity',
        class: GridView::class,
        permission: 'VIEW',
        groupName: 'commerce'
    )]
    #[\Override]
    public function defaultAction($id, $default = false, $gridName = null)
    {
        return parent::defaultAction($id, $default, $gridName);
    }

    #[\Override]
    public function getManager()
    {
        return $this->container->get('oro_customer.grid_view.manager.api');
    }

    #[\Override]
    protected function isGridViewPublishGranted()
    {
        return $this->isGranted('oro_customer_frontend_gridview_publish');
    }
}
