<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddressProvider
{
    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var FragmentHandler */
    protected $fragmentHandler;

    /** @var string */
    protected $entityClass;

    /** @var string */
    protected $listRouteName;

    /** @var string */
    protected $createRouteName;

    /** @var string */
    protected $updateRouteName;

    /** @var bool */
    protected $defaultOnly;

    /**
     * @param UrlGeneratorInterface $router
     * @param FragmentHandler $fragmentHandler
     */
    public function __construct(UrlGeneratorInterface $router, FragmentHandler $fragmentHandler)
    {
        $this->router = $router;
        $this->fragmentHandler = $fragmentHandler;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @param string $listRouteName
     * @param bool $defaultOnly
     */
    public function setListRouteName($listRouteName, $defaultOnly = false)
    {
        $this->listRouteName = $listRouteName;
        $this->defaultOnly = $defaultOnly;
    }

    /**
     * @param string $createRouteName
     */
    public function setCreateRouteName($createRouteName)
    {
        $this->createRouteName = $createRouteName;
    }

    /**
     * @param string $updateRouteName
     */
    public function setUpdateRouteName($updateRouteName)
    {
        $this->updateRouteName = $updateRouteName;
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    public function getComponentOptions($entity)
    {
        if (!$this->listRouteName || !$this->createRouteName || !$this->updateRouteName) {
            throw new \UnexpectedValueException(
                "Missing value. Make sure that \"list\", \"create\" and \"update\" route names are not empty."
            );
        }
        
        if (!$entity instanceof $this->entityClass) {
            throw new \UnexpectedValueException(
                sprintf('Entity should be instanceof "%s", "%s" given.', $this->entityClass, gettype($entity))
            );
        }

        $params = ['entityId' => $entity->getId()];

        if ($this->defaultOnly) {
            $params['default_only'] = true;
        }

        $addressListUrl = $this->router->generate($this->listRouteName, $params);
        $addressCreateUrl = $this->router->generate($this->createRouteName, ['entityId' => $entity->getId()]);

        return [
            'entityId' => $entity->getId(),
            'addressListUrl' => $addressListUrl,
            'addressCreateUrl' => $addressCreateUrl,
            'addressUpdateRouteName' => $this->updateRouteName,
            'currentAddresses' => $this->fragmentHandler->render($addressListUrl),
        ];
    }
}
