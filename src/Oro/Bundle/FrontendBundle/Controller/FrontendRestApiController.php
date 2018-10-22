<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Component\ChainProcessor\ActionProcessorInterface;
use Oro\Bundle\ApiBundle\Controller\AbstractRestApiController;
use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\ApiBundle\Processor\Delete\DeleteContext;
use Oro\Bundle\ApiBundle\Processor\DeleteList\DeleteListContext;
use Oro\Bundle\ApiBundle\Processor\Get\GetContext;
use Oro\Bundle\ApiBundle\Processor\GetList\GetListContext;
use Oro\Bundle\ApiBundle\Processor\Update\UpdateContext;
use Oro\Bundle\ApiBundle\Request\RestFilterValueAccessor;

/**
 * @internal Will be reimplemented in 3.0
 */
class FrontendRestApiController extends AbstractRestApiController
{
    /**
     * Get a list of entities
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Get entities",
     *     resource=true,
     *     views={"frontend_rest_json_api"}
     * )
     *
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var GetListContext $context */
        $context = $this->getContext($processor, $request);
        $context->setFilterValues($this->getFilterValueAccessor($request));

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Get an entity
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Get an entity",
     *     resource=true,
     *     views={"frontend_rest_json_api"}
     * )
     *
     * @return Response
     */
    public function getAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var GetContext $context */
        $context = $this->getContext($processor, $request);
        $context->setId($request->attributes->get('id'));
        $context->setFilterValues($this->getFilterValueAccessor($request));

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Delete an entity
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Delete an entity",
     *     resource=true,
     *     views={"frontend_rest_json_api"}
     * )
     *
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var DeleteContext $context */
        $context = $this->getContext($processor, $request);
        $context->setId($request->attributes->get('id'));

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Delete a list of entities
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Delete entities",
     *     resource=true,
     *     views={"frontend_rest_json_api"}
     * )
     *
     * @return Response
     */
    public function cdeleteAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var DeleteListContext $context */
        $context = $this->getContext($processor, $request);
        $context->setFilterValues($this->getFilterValueAccessor($request));

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Update an entity
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Update an entity",
     *     resource=true,
     *     views={"frontend_rest_json_api"}
     * )
     *
     * @return Response
     */
    public function patchAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var UpdateContext $context */
        $context = $this->getContext($processor, $request);
        $context->setId($request->attributes->get('id'));
        $context->setRequestData($request->request->all());

        $processor->process($context);

        return $this->buildResponse($context);
    }

    /**
     * Create an entity
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     description="Create an entity",
     *     resource=true,
     *     views={"frontend_rest_json_api"}
     * )
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $processor = $this->getProcessor($request);
        /** @var CreateContext $context */
        $context = $this->getContext($processor, $request);
        $context->setRequestData($request->request->all());

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
