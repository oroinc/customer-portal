<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Controller\Frontend;

use Oro\Bundle\DataGridBundle\Controller\GridController;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AJAX controller for grid mass actions.
 */
class AjaxGridMassActionController extends AbstractController
{
    #[Route(
        path: '/{gridName}/massAction/{actionName}',
        name: 'oro_frontend_datagrid_mass_action',
        requirements: ['gridName' => '[\w\:\-]+', 'actionName' => '[\w\-]+']
    )]
    #[CsrfProtection]
    public function __invoke(Request $request, string $gridName, string $actionName): Response
    {
        return $this->forward(
            GridController::class . '::massActionAction',
            ['gridName' => $gridName, 'actionName' => $actionName],
            $request->query->all()
        );
    }
}
