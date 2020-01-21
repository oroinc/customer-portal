<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\FrontendBundle\Api\ResourceTypeResolver;

class ResourceTypeResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveTypeWithoutRouteParameters()
    {
        $resourceType = 'test_type';

        $resolver = new ResourceTypeResolver($resourceType);
        self::assertEquals(
            $resourceType,
            $resolver->resolveType('/test', ['key1' => '1'], new RequestType([]))
        );
    }

    public function testResolveTypeWithRouteParametersAndAllParametersExist()
    {
        $resourceType = 'test_type';

        $resolver = new ResourceTypeResolver($resourceType, ['key1', 'key3']);
        self::assertEquals(
            $resourceType,
            $resolver->resolveType('/test', ['key1' => '1', 'key2' => '2', 'key3' => '3'], new RequestType([]))
        );
    }

    public function testResolveTypeWithRouteParametersAndNotAllParametersExist()
    {
        $resourceType = 'test_type';

        $resolver = new ResourceTypeResolver($resourceType, ['key1', 'key4']);
        self::assertNull(
            $resolver->resolveType('/test', ['key1' => '1', 'key2' => '2', 'key3' => '3'], new RequestType([]))
        );
    }
}
