<?php

namespace Oro\Bundle\CustomerBundle\Security\Firewall;

use Oro\Bundle\ApiBundle\Model\Error;
use Oro\Bundle\ApiBundle\Processor\ActionProcessorBagInterface;
use Oro\Bundle\ApiBundle\Processor\Options\OptionsContext;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\ApiActionGroup;
use Oro\Bundle\ApiBundle\Request\ErrorCompleterRegistry;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\Rest\RestRoutes;
use Oro\Bundle\ApiBundle\Request\RestRequestHeaders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The service that is used to check whether an access for non-authenticated visitors is granted for an API request.
 */
class ApiAnonymousCustomerUserAuthenticationDecisionMaker
{
    private array $apiResources;
    private ActionProcessorBagInterface $actionProcessorBag;
    private RestRoutes $routes;
    private ErrorCompleterRegistry $errorCompleterRegistry;

    public function __construct(
        array $apiResources,
        ActionProcessorBagInterface $actionProcessorBag,
        RestRoutes $routes,
        ErrorCompleterRegistry $errorCompleterRegistry
    ) {
        $this->apiResources = $apiResources;
        $this->actionProcessorBag = $actionProcessorBag;
        $this->routes = $routes;
        $this->errorCompleterRegistry = $errorCompleterRegistry;
    }

    public function isAnonymousCustomerUserAllowed(Request $request): bool
    {
        $entityType = $request->attributes->get('entity');
        if (!$entityType) {
            return false;
        }

        $processor = $this->actionProcessorBag->getProcessor(ApiAction::OPTIONS);
        /** @var OptionsContext $context */
        $context = $processor->createContext();
        $context->getRequestType()->add(RequestType::REST);
        $context->getRequestType()->add('frontend');
        $context->setMainRequest(true);
        $context->setRequestHeaders(new RestRequestHeaders($request));
        $context->setActionType($this->getActionType($request->attributes->get('_route')));
        $context->setClassName($entityType);
        $context->setFirstGroup(ApiActionGroup::INITIALIZE);
        $context->setLastGroup(ApiActionGroup::RESOURCE_CHECK);
        $context->setSoftErrorsHandling(true);
        $processor->process($context);

        if ($context->hasErrors()
            && $this->getStatusCode($context->getErrors(), $context->getRequestType()) === Response::HTTP_NOT_FOUND
        ) {
            /**
             * an API resource is not enabled
             * @see \Oro\Bundle\ApiBundle\Processor\Shared\ValidateEntityTypeFeature
             */
            return true;
        }

        $entityClass = $context->getClassName();

        return
            $entityClass !== $entityType
            && \in_array($entityClass, $this->apiResources, true);
    }

    private function getActionType(?string $route): string
    {
        if (!$route) {
            return OptionsContext::ACTION_TYPE_LIST;
        }

        switch ($route) {
            case $this->routes->getListRouteName():
                return OptionsContext::ACTION_TYPE_LIST;
            case $this->routes->getItemRouteName():
            case $this->routes->getRelationshipRouteName():
            case $this->routes->getSubresourceRouteName():
                return OptionsContext::ACTION_TYPE_ITEM;
        }

        return OptionsContext::ACTION_TYPE_LIST;
    }

    private function getStatusCode(array $errors, RequestType $requestType): int
    {
        $errorCompleter = $this->errorCompleterRegistry->getErrorCompleter($requestType);

        $statusCode = null;
        /** @var Error $error */
        foreach ($errors as $error) {
            $errorCompleter->complete($error, $requestType);
            $code = $error->getStatusCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR;
            if (null === $statusCode || $statusCode < $code) {
                $statusCode = $code;
            }
        }

        return $statusCode;
    }
}
