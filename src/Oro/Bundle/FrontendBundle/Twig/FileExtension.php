<?php

namespace Oro\Bundle\FrontendBundle\Twig;

use Oro\Bundle\AttachmentBundle\Twig\FileExtension as BaseFileExtension;
use Oro\Bundle\FrontendBundle\Manager\AttachmentManager;

class FileExtension extends BaseFileExtension
{
    /**
     * @return AttachmentManager
     */
    protected function getAttachmentManager()
    {
        return $this->container->get('oro_frontend.attachment.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'oro_frontend_file_view',
                [$this, 'getFileView'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new \Twig_SimpleFunction(
                'oro_frontend_image_view',
                [$this, 'getImageView'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new \Twig_SimpleFunction(
                'oro_frontend_debug_routes',
                [$this, 'getIsDebugRoutes'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            )
        ];
    }

    /**
     * @return bool
     */
    public function getIsDebugRoutes()
    {
        return $this->container->getParameter('oro_frontend.debug_routes');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_frontend_attachment_file';
    }
}
