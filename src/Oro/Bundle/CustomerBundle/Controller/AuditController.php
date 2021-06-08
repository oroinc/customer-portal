<?php

namespace Oro\Bundle\CustomerBundle\Controller;

// Disabled as per BAP-9497
//class AuditController extends Controller
//{
//    /**
//     * @Route(
//     *      "/history/{entity}/{id}/{_format}",
//     *      name="oro_customer_frontend_dataaudit_history",
//     *      requirements={"entity"="[a-zA-Z0-9_]+", "id"="\d+"},
//     *      defaults={"entity"="entity", "id"=0, "_format" = "html"}
//     * )
//     * @Template("@OroDataAudit/Audit/widget/history.html.twig")
//     * @Acl(
//     *      id="oro_customer_dataaudit_history",
//     *      type="action",
//     *      label="oro.customer.dataaudit.module_label",
//     *      group_name="commerce"
//     * )
//     * @param string $entity
//     * @param string $id
//     * @return array
//     */
//    public function historyAction($entity, $id)
//    {
//        return [
//            'gridName' => 'frontend-audit-history-grid',
//            'entityClass' => $entity,
//            'entityId' => $id,
//        ];
//    }
//}
