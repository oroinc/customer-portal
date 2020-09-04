<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Unit\Request;

use Oro\Bundle\FrontendAttachmentBundle\Request\MediaCacheRequestHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaCacheRequestHelperTest extends \PHPUnit\Framework\TestCase
{
    private const MEDIACACHE_PREFIX = 'media/cache';

    /** @var RequestStack */
    private $requestStack;

    /** @var MediaCacheRequestHelper */
    private $helper;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();

        $this->helper = new MediaCacheRequestHelper($this->requestStack, self::MEDIACACHE_PREFIX);
    }

    public function testIsFrontendRequestWithoutPath(): void
    {
        $this->assertFalse($this->helper->isMediaCacheRequest());
    }

    /**
     * @dataProvider isMediaCacheRequestDataProvider
     *
     * @param string $path
     * @param bool $isMediaCache
     */
    public function testIsMediaCacheRequest(string $path, bool $isMediaCache): void
    {
        $this->requestStack->push(Request::create($path));

        $this->assertSame($isMediaCache, $this->helper->isMediaCacheRequest());
    }

    /**
     * @dataProvider isMediaCacheRequestDataProvider
     *
     * @param string $path
     * @param bool $isMediaCache
     */
    public function testIsMediaCacheRequestWhenRequestProvided(string $path, bool $isMediaCache): void
    {
        $this->assertSame($isMediaCache, $this->helper->isMediaCacheRequest(Request::create($path)));
    }


    /**
     * @return array
     */
    public function isMediaCacheRequestDataProvider(): array
    {
        return [
            'mediacache' => [
                'path' => self::MEDIACACHE_PREFIX . '/sample-url',
                'isMediaCache' => true,
            ],
            'not mediacache' => [
                'path' => '/sample-url',
                'isMediaCache' => false,
            ],
            'url with mediacache part' => [
                'path' => '/sample-url/' . self::MEDIACACHE_PREFIX,
                'isMediaCache' => false,
            ],
            'mediacache prefix without slash' => [
                'path' => self::MEDIACACHE_PREFIX,
                'isMediaCache' => false,
            ],
            'mediacache prefix with slash' => [
                'path' => self::MEDIACACHE_PREFIX . '/',
                'isMediaCache' => true,
            ],
        ];
    }
}
