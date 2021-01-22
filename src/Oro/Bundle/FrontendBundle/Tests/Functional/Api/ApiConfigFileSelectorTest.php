<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api;

use Oro\Bundle\ApiBundle\Provider\EntityAliasResolverRegistry;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\TestFrameworkBundle\Entity\Item as TestFrontendOnlyEntity;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ApiConfigFileSelectorTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    /**
     * Tests that "request_type: ['frontend']" has higher priority than "request_type: ['rest', '!json_api']"
     * in the oro_api.config_files configuration.
     */
    public function testFrontendApiConfigFileIsLoadedForPlainRestApi()
    {
        /** @var EntityAliasResolverRegistry $entityAliasResolverRegistry */
        $entityAliasResolverRegistry = self::getContainer()->get('oro_api.entity_alias_resolver_registry');
        $entityAliasResolver = $entityAliasResolverRegistry->getEntityAliasResolver(
            new RequestType([RequestType::REST, 'frontend'])
        );
        self::assertEquals(
            TestFrontendOnlyEntity::class,
            $entityAliasResolver->getClassByPluralAlias('testfrontendonlyentities')
        );
    }
}
