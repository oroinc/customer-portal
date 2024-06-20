<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\OpenApi;

use Oro\Bundle\ApiBundle\ApiDoc\DocumentationProviderInterface;
use Oro\Bundle\ApiBundle\ApiDoc\RestDocViewDetector;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Assert\ArrayContainsConstraint;

class FrontendOpenApiSpecificationTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    protected function assertOpenApiSpecificationEquals(
        array $expected,
        array $actual,
        string $view = 'frontend_rest_json_api'
    ): void {
        self::assertArrayNotHasKey(
            'description',
            $expected['info'],
            'The description of OpenAPI specification is validated automatically.'
            . ' Please remove the "description" element from "info" section of the expected data.'
        );

        $allowedXParameters = $this->getXParameters($actual);
        $expected = $this->prepareExpectedData($expected, $allowedXParameters);

        $actualDescription = $actual['info']['description'];
        unset($actual['info']['description']);

        self::assertThat($actual, new ArrayContainsConstraint($expected, true));

        /** @var RestDocViewDetector $docViewDetector */
        $docViewDetector = self::getContainer()->get('oro_api.rest.doc_view_detector');
        $docViewDetector->setView($view);
        /** @var DocumentationProviderInterface $documentationProvider */
        $documentationProvider = self::getContainer()->get('oro_api.api_doc.documentation_provider');
        $expectedDescription = $documentationProvider->getDocumentation($docViewDetector->getRequestType());
        self::assertStringEndsWith(
            $expectedDescription,
            $actualDescription,
            'The description of OpenAPI specification is not valid.'
        );
    }

    private function getXParameters(array $data): array
    {
        $xParameters = [];
        $parameters = $data['components']['parameters'] ?? [];
        foreach ($parameters as $name => $item) {
            if (str_starts_with($name, 'x')) {
                $xParameters[] = $name;
            }
        }

        return $xParameters;
    }

    /**
     * Updates expected data to be able to use them in both CE and EE versions of the application.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function prepareExpectedData(array $data, array $allowedXParameters): array
    {
        $toRemoveParamNames = [];
        $parameters = $data['components']['parameters'] ?? [];
        foreach ($parameters as $name => $item) {
            if (str_starts_with($name, 'x') && !\in_array($name, $allowedXParameters, true)) {
                $toRemoveParamNames[] = $name;
            }
        }
        foreach ($toRemoveParamNames as $toRemoveParamName) {
            unset($data['components']['parameters'][$toRemoveParamName]);
            $toRemoveParamRef = '#/components/parameters/' . $toRemoveParamName;
            foreach ($data['paths'] as $url => $paths) {
                foreach ($paths as $method => $path) {
                    $toRemove = [];
                    foreach ($path['parameters'] as $paramIndex => $param) {
                        if (($param['$ref'] ?? null) === $toRemoveParamRef) {
                            $toRemove[] = $paramIndex;
                        }
                    }
                    foreach ($toRemove as $toRemoveParamIndex) {
                        unset($data['paths'][$url][$method]['parameters'][$toRemoveParamIndex]);
                    }
                    $data['paths'][$url][$method]['parameters'] = array_values(
                        $data['paths'][$url][$method]['parameters']
                    );
                }
            }
        }

        return $data;
    }
}
