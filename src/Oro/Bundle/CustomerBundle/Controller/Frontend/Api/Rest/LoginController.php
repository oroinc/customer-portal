<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\Encoder\JsonEncode;

/**
 * Implements "POST /api/login" REST API resource.
 * @internal Will be reimplemented in 3.0
 */
class LoginController extends Controller
{
    /**
     * Validates the customer user email and password, and if the credentials are valid, returns the API access key
     * that can be used for subsequent API requests.
     *
     * Example of the request:
     *
     * ```JSON
     * {
     *   "meta": {
     *     "email": "user@example.com",
     *     "password": "123"
     *   }
     * }
     * ```
     *
     * Example of the response:
     *
     * ```JSON
     * {
     *   "meta": {
     *     "apiKey": "22b7172bbf9cdcfaa7bac067dabcb07d358ce511"
     *   }
     * }
     * ```
     *
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Get API Access Key",
     *     views={"frontend_rest_json_api"},
     *     section="login",
     *     input={
     *          "class"="stdClass",
     *          "fields"={
     *              {
     *                  "name"="email",
     *                  "dataType"="string",
     *                  "description"="The customer user email."
     *              },
     *              {
     *                  "name"="password",
     *                  "dataType"="string",
     *                  "description"="The customer user password."
     *              }
     *          }
     *     },
     *     output={
     *          "class"="stdClass",
     *          "fields"={
     *              {
     *                  "name"="apiKey",
     *                  "dataType"="string",
     *                  "description"="The API access key."
     *              }
     *          }
     *     },
     *     statusCodes={
     *          200="Returned when the credentials are valid and API access key exists",
     *          400="Returned when the request data is not valid",
     *          403="Returned when the credentials are not valid or API access key does not exist",
     *          500="Returned when an unexpected error occurs"
     *     }
     * )
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $requestData = $request->request->all();
        $errors = [];
        if (empty($requestData['meta']['email'])) {
            $errors[] = [
                'status' => '400',
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/meta/email']
            ];
        }
        if (empty($requestData['meta']['password'])) {
            $errors[] = [
                'status' => '400',
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/meta/password']
            ];
        }
        if (!empty($errors)) {
            return $this->buildResponse(['errors' => $errors], 400);
        }

        $email = $requestData['meta']['email'];
        $password = $requestData['meta']['password'];
        try {
            $authenticatedUser = $this->authenticate($email, $password)->getUser();
            if (!$authenticatedUser instanceof CustomerUser) {
                throw new AccessDeniedException('The login via API is not supported for this user.');
            }

            $apiKey = $this->getApiKey($authenticatedUser);
            if (!$apiKey) {
                if (!$this->isApiKeyGenerationEnabled()) {
                    throw new AccessDeniedException('The API access key was not generated for this user.');
                }
                $apiKey = $this->generateApiKey($authenticatedUser);
            }

            return $this->buildResponse(['meta' => ['apiKey' => $apiKey]]);
        } catch (AccessDeniedException $e) {
            return $this->buildResponse(
                [
                    'errors' => [
                        ['status' => '403', 'title' => 'access denied exception', 'detail' => $e->getMessage()]
                    ]
                ],
                403
            );
        } catch (\Exception $e) {
            return $this->buildResponse(
                [
                    'errors' => [
                        ['status' => '500', 'title' => 'unexpected exception', 'detail' => $e->getMessage()]
                    ]
                ],
                500
            );
        }
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return TokenInterface
     */
    private function authenticate($email, $password)
    {
        $token = new UsernamePasswordToken($email, $password, 'frontend');
        $authenticationProvider = $this->get('oro_customer.api.frontend.authentication_provider');
        if (!$authenticationProvider->supports($token)) {
            throw new \LogicException(
                'Invalid authentication provider. The provider key is "frontend".'
            );
        }

        try {
            return $authenticationProvider->authenticate($token);
        } catch (AuthenticationException $e) {
            throw new AccessDeniedException(sprintf(
                'The user authentication fails. Reason: %s',
                $this->get('translator')->trans($e->getMessageKey(), [], 'security')
            ));
        }
    }

    /**
     * @return bool
     */
    private function isApiKeyGenerationEnabled()
    {
        return (bool)$this->get('oro_config.manager')->get('oro_customer.api_key_generation_enabled');
    }

    /**
     * @param CustomerUser $user
     *
     * @return string|null
     */
    private function getApiKey(CustomerUser $user)
    {
        $apiKey = $user->getApiKeys()->first();
        if (!$apiKey) {
            return null;
        }

        return $apiKey->getApiKey();
    }

    /**
     * @param CustomerUser $user
     *
     * @return string
     */
    private function generateApiKey(CustomerUser $user)
    {
        $apiKey = new CustomerUserApi();
        $apiKey->setApiKey($apiKey->generateKey());

        $user->addApiKey($apiKey);

        $em = $this->get('doctrine')->getManagerForClass(CustomerUser::class);
        $em->persist($apiKey);
        $em->flush();

        return $apiKey->getApiKey();
    }

    /**
     * @param mixed $data
     * @param int   $statusCode
     *
     * @return Response
     */
    private function buildResponse($data, $statusCode = 200)
    {
        $view = View::create($data, $statusCode);

        $handler = $this->get('fos_rest.view_handler');
        $handler->registerHandler(
            'json',
            function (ViewHandler $viewHandler, View $view, Request $request, $format) {
                $response = $view->getResponse();
                $encoder = new JsonEncode();
                $response->setContent($encoder->encode($view->getData(), $format));
                $response->headers->set('Content-Type', 'application/vnd.api+json');

                return $response;
            }
        );

        return $handler->handle($view);
    }
}
