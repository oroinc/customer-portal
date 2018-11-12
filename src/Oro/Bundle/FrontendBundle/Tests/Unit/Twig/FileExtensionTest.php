<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Twig;

use Oro\Bundle\AttachmentBundle\Tests\Unit\Fixtures\TestAttachment;
use Oro\Bundle\AttachmentBundle\Tests\Unit\Fixtures\TestClass;
use Oro\Bundle\AttachmentBundle\Tests\Unit\Fixtures\TestTemplate;
use Oro\Bundle\FrontendBundle\Manager\AttachmentManager;
use Oro\Bundle\FrontendBundle\Twig\FileExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class FileExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var FileExtension */
    protected $extension;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AttachmentManager */
    protected $manager;

    /** @var TestAttachment */
    protected $attachment;

    public function setUp()
    {
        $this->manager = $this->getMockBuilder(AttachmentManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_frontend.attachment.manager', $this->manager)
            ->getContainer($this);

        $this->extension = new FileExtension($container);
        $this->attachment = new TestAttachment();
    }

    public function testGetName()
    {
        $this->assertEquals('oro_frontend_attachment_file', $this->extension->getName());
    }

    public function testGetEmptyFileView()
    {
        $parentEntity = new TestClass();
        $environment = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            '',
            self::callTwigFunction(
                $this->extension,
                'oro_frontend_file_view',
                [$environment, $parentEntity, $this->attachment]
            )
        );
    }

    public function testGetFileView()
    {
        $parentEntity = new TestClass();
        $parentField = 'test_field';
        $this->attachment->setFilename('test.doc');
        $environment = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $template = new TestTemplate(new \Twig_Environment());
        $environment->expects($this->once())
            ->method('loadTemplate')
            ->will($this->returnValue($template));
        $this->manager->expects($this->once())
            ->method('getAttachmentIconClass')
            ->with($this->attachment);
        $this->manager->expects($this->once())
            ->method('getFileUrl');

        self::callTwigFunction(
            $this->extension,
            'oro_frontend_file_view',
            [$environment, $parentEntity, $parentField, $this->attachment]
        );
    }

    public function testGetEmptyImageView()
    {
        $environment = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            '',
            self::callTwigFunction($this->extension, 'oro_frontend_image_view', [$environment, $this->attachment])
        );
    }

    public function testGetImageView()
    {
        $parentEntity = new TestClass();
        $this->attachment->setFilename('test.doc');
        $environment = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $template = new TestTemplate(new \Twig_Environment());
        $environment->expects($this->once())
            ->method('loadTemplate')
            ->will($this->returnValue($template));
        $this->manager->expects($this->once())
            ->method('getResizedImageUrl')
            ->with($this->attachment, 16, 16);
        $this->manager->expects($this->once())
            ->method('getFileUrl');

        self::callTwigFunction(
            $this->extension,
            'oro_frontend_image_view',
            [$environment, $parentEntity, $this->attachment]
        );
    }
}
