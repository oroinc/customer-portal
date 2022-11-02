<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\DataGridBundle\Controller\Api\Rest\GridViewController as BaseGridViewController;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
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
     * @Acl(
     *     id="oro_customer_frontend_gridview_create",
     *     type="entity",
     *     class="OroCustomerBundle:GridView",
     *     permission="CREATE",
     *     group_name="commerce"
     * )
     */
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
     * @Acl(
     *     id="oro_customer_frontend_gridview_update",
     *     type="entity",
     *     class="OroCustomerBundle:GridView",
     *     permission="EDIT",
     *     group_name="commerce"
     * )
     */
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
     * @Acl(
     *     id="oro_customer_frontend_gridview_delete",
     *     type="entity",
     *     class="OroCustomerBundle:GridView",
     *     permission="DELETE",
     *     group_name="commerce"
     * )
     */
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
     * @Acl(
     *     id="oro_customer_frontend_gridview_view",
     *     type="entity",
     *     class="OroCustomerBundle:GridView",
     *     permission="VIEW",
     *     group_name="commerce"
     * )
     */
    public function defaultAction($id, $default = false, $gridName = null)
    {
        return parent::defaultAction($id, $default, $gridName);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_customer.grid_view.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    protected function isGridViewPublishGranted()
    {
        return $this->isGranted('oro_customer_frontend_gridview_publish');
    }
}
