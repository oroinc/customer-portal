<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Component\ChainProcessor\ActionProcessorInterface;
use Oro\Bundle\ApiBundle\Controller\AbstractRestApiSubresourceController;
use Oro\Bundle\ApiBundle\Processor\Subresource\GetSubresource\GetSubresourceContext;
use Oro\Bundle\ApiBundle\Request\RestFilterValueAccessor;

/**
 * @internal Will be removed in 3.0
 */
class FrontendRestApiSubresourceController extends AbstractRestApiSubresourceController
{
    /**
     * Get an entity (for to-one association) or a list of entities (for to-many association)
     * connected to the given entity by the given association
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Get related entity or entities",
     *     resource=true,
     *     views={"frontend_rest_json_api"}
     * )
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var GetSubresourceContext $context */
        $context = $this->getContext($processor, $request);
        $context->setFilterValues($this->getFilterValueAccessor($request));

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContext(ActionProcessorInterface $processor, Request $request)
    {
        $context = parent::getContext($processor, $request);
        $context->getRequestType()->add('frontend');

        return $context;
    }
}
