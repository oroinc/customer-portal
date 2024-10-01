<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;

abstract class FrontendActionTestCase extends ActionTestCase
{
    #[\Override]
    protected function getOperationExecutionRoute(): string
    {
        return 'oro_frontend_action_operation_execute';
    }

    #[\Override]
    protected function getOperationDialogRoute(): string
    {
        return 'oro_frontend_action_widget_form';
    }
}
