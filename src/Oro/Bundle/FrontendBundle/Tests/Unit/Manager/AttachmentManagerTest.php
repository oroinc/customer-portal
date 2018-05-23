<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Manager;

use Oro\Bundle\AttachmentBundle\Tests\Unit\Fixtures\TestAttachment;
use Oro\Bundle\AttachmentBundle\Tests\Unit\Fixtures\TestClass;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\FrontendBundle\Manager\AttachmentManager;
use Symfony\Component\Routing\RouterInterface;

class AttachmentManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AttachmentManager  */
    protected $attachmentManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|RouterInterface */
    protected $router;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|AssociationManager */
    protected $associationManager;

    /** @var TestAttachment */
    protected $attachment;

    public function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $fileIcons = [
            'default' => 'icon_default',
            'txt' => 'icon_txt'
        ];

        $this->attachment = new TestAttachment();
        $this->attachment->setFilename('testFile.txt');
        $this->attachment->setOriginalFilename('testFile.txt');

        $this->associationManager = $this
            ->getMockBuilder('Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attachmentManager = new AttachmentManager(
            $this->router,
            $fileIcons,
            $this->associationManager,
            true,
            true
        );
    }

    public function testGetFileUrl()
    {
        $this->attachment->setId(1);
        $this->attachment->setExtension('txt');
        $this->attachment->setOriginalFilename('testFile.withForwardSlash?.txt');
        $fieldName = 'testField';
        $parentEntity = new TestClass();
        $expectsString = 'T3JvXEJ1bmRsZVxBdHRhY2htZW50QnVuZGxlXFRlc3RzXFVuaXRcRml4dHVyZXNcVGVzdENsYXNzfHRlc3RG'.
            'aWVsZHwxfGRvd25sb2FkfHRlc3RGaWxlLndpdGhGb3J3YXJkU2xhc2g_LnR4dA==';
        //Underscore should replace / character
        $this->router->expects($this->once())
            ->method('generate')
            ->with(
                AttachmentManager::ATTACHMENT_FILE_ROUTE,
                [
                    'codedString' => $expectsString,
                    'extension' => 'txt'
                ],
                RouterInterface::ABSOLUTE_URL
            );
        $this->attachmentManager->getFileUrl($parentEntity, $fieldName, $this->attachment, 'download', true);
    }
}
