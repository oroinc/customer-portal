<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Oro\Bundle\ApiBundle\ApiDoc\CachingApiDocExtractor;
use Oro\Bundle\ApiBundle\Request\ApiActions;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\TestFrameworkBundle\Entity\TestFrameworkEntityInterface;

class DocumentationTest extends FrontendRestJsonApiTestCase
{
    private const VIEW = 'frontend_rest_json_api';

    private const RESOURCE_ERROR_COUNT = 3;

    /**
     * @see \Oro\Bundle\ApiBundle\ApiDoc\ResourceDocProvider::TEMPLATES
     * @var array
     */
    private const DEFAULT_DOCUMENTATION = [
        ApiActions::GET                 => 'Get an entity',
        ApiActions::GET_LIST            => 'Get a list of entities',
        ApiActions::DELETE              => 'Delete an entity',
        ApiActions::DELETE_LIST         => 'Delete a list of entities',
        ApiActions::CREATE              => 'Create an entity',
        ApiActions::UPDATE              => 'Update an entity',
        ApiActions::GET_SUBRESOURCE     => [
            'Get a related entity',
            'Get a list of related entities'
        ],
        ApiActions::GET_RELATIONSHIP    => 'Get the relationship data',
        ApiActions::DELETE_RELATIONSHIP => 'Delete the specified members from the relationship',
        ApiActions::ADD_RELATIONSHIP    => 'Add the specified members to the relationship',
        ApiActions::UPDATE_RELATIONSHIP => [
            'Update the relationship',
            'Completely replace every member of the relationship'
        ],
    ];

    /**
     * This test method is used to avoid unnecessary warming up of documentation cache in all other test methods.
     */
    public function testWarmUpCache()
    {
        $apiDocExtractor = $this->getExtractor();
        if ($apiDocExtractor instanceof CachingApiDocExtractor) {
            $apiDocExtractor->warmUp(self::VIEW);
        }
    }

    /**
     * @depends testWarmUpCache
     */
    public function testDocumentation()
    {
        $missingDocs = [];
        $docs = $this->getExtractor()->all(self::VIEW);
        foreach ($docs as $doc) {
            /** @var ApiDoc $annotation */
            $annotation = $doc['annotation'];
            $definition = $annotation->toArray();
            $route = $annotation->getRoute();

            $entityType = $route->getDefault('entity');
            $action = $route->getDefault('_action');
            $association = $route->getDefault('association');
            if ($entityType && $action) {
                $entityClass = $this->getEntityClass($entityType);
                if ($entityClass && !$this->isSkippedEntity($entityClass, $entityType)) {
                    $resourceMissingDocs = $this->checkApiResource($definition, $entityClass, $action, $association);
                    if (!empty($resourceMissingDocs)) {
                        $resource = sprintf('%s %s', $definition['method'], $definition['uri']);
                        $missingDocs[$entityClass][$resource] = $resourceMissingDocs;
                    }
                }
            }
        }

        if (!empty($missingDocs)) {
            self::fail($this->buildFailMessage($missingDocs));
        }
    }

    /**
     * @param string $entityClass
     * @param string $entityType
     *
     * @return bool
     */
    protected function isSkippedEntity($entityClass, $entityType)
    {
        return
            is_a($entityClass, TestFrameworkEntityInterface::class, true)
            || 0 === strpos($entityType, 'testapi')
            || (// custom entities (entities from "Extend\Entity" namespace), except enums
                0 === strpos($entityClass, ExtendHelper::ENTITY_NAMESPACE)
                && 0 !== strpos($entityClass, ExtendHelper::ENTITY_NAMESPACE . 'EV_')
            );
    }

    /**
     * @param array  $definition
     * @param string $entityClass
     * @param string $action
     * @param string $association
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function checkApiResource(array $definition, $entityClass, $action, $association)
    {
        $missingDocs = [];
        if (empty($definition['description'])) {
            $missingDocs[] = 'Empty description';
        }
        if (empty($definition['documentation'])) {
            $missingDocs[] = 'Empty documentation';
        } elseif (isset(self::DEFAULT_DOCUMENTATION[$action])
            && in_array($definition['documentation'], (array)self::DEFAULT_DOCUMENTATION[$action], true)
        ) {
            $missingDocs[] = sprintf(
                'Missing documentation. Default value is used: "%s"',
                $definition['documentation']
            );
        }
        if (!empty($definition['parameters'])) {
            foreach ($definition['parameters'] as $name => $item) {
                if (empty($item['description'])) {
                    $missingDocs[] = sprintf('Input Field: %s. Empty description.', $name);
                }
            }
        }
        if (!empty($definition['filters'])) {
            foreach ($definition['filters'] as $name => $item) {
                if (empty($item['description'])) {
                    $missingDocs[] = sprintf('Filter: %s. Empty description.', $name);
                }
            }
        }
        if (!$association && !empty($definition['response'])) {
            foreach ($definition['response'] as $name => $item) {
                if (empty($item['description'])) {
                    $missingDocs[] = sprintf('Output Field: %s. Empty description.', $name);
                }
            }
        }

        return $missingDocs;
    }

    /**
     * @param array $missingDocs
     *
     * @return string
     */
    protected function buildFailMessage(array $missingDocs)
    {
        $message = sprintf(
            'Missing documentation for %s entit%s.' . PHP_EOL . PHP_EOL,
            count($missingDocs),
            count($missingDocs) > 1 ? 'ies' : 'y'
        );
        foreach ($missingDocs as $entityClass => $resources) {
            $message .= sprintf('%s' . PHP_EOL, $entityClass);
            foreach ($resources as $resource => $errors) {
                $message .= sprintf('    %s' . PHP_EOL, $resource);
                $i = 0;
                $errorCount = count($errors);
                foreach ($errors as $error) {
                    $message .= sprintf('        %s' . PHP_EOL, $error);
                    $i++;
                    if (self::RESOURCE_ERROR_COUNT === $i && $errorCount > self::RESOURCE_ERROR_COUNT + 2) {
                        $message .= sprintf('        and others %d errors ...' . PHP_EOL, $errorCount - $i);
                        break;
                    }
                }
            }
        }

        return $message;
    }

    /**
     * @return ApiDocExtractor
     */
    protected function getExtractor()
    {
        return self::getContainer()->get('nelmio_api_doc.extractor.api_doc_extractor');
    }
}
