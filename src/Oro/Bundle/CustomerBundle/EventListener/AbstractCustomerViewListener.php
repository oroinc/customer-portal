<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Abstract class for the listener which are used to display additional block on the Customer or CustomerUser view pages
 */
abstract class AbstractCustomerViewListener
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(
        TranslatorInterface $translator,
        DoctrineHelper $doctrineHelper,
        RequestStack $requestStack
    ) {
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
        $this->requestStack = $requestStack;
    }

    public function onCustomerView(BeforeListRenderEvent $event)
    {
        /** @var Customer $customer */
        $customer = $this->getEntityFromRequestId(Customer::class);
        if ($customer) {
            $template = $event->getEnvironment()->render(
                $this->getCustomerViewTemplate(),
                ['entity' => $customer]
            );
            $this->addRequestForQuotesBlock(
                $event->getScrollData(),
                $template,
                $this->translator->trans($this->getCustomerLabel())
            );
        }
    }

    public function onCustomerUserView(BeforeListRenderEvent $event)
    {
        /** @var CustomerUser $customer */
        $customer = $this->getEntityFromRequestId(CustomerUser::class);
        if ($customer) {
            $template = $event->getEnvironment()->render(
                $this->getCustomerUserViewTemplate(),
                ['entity' => $customer]
            );
            $this->addRequestForQuotesBlock(
                $event->getScrollData(),
                $template,
                $this->translator->trans($this->getCustomerUserLabel())
            );
        }
    }

    /**
     * @param $className
     * @return null|object
     */
    protected function getEntityFromRequestId($className)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }

        $entityId = (int)$request->get('id');
        if (!$entityId) {
            return null;
        }

        $entity = $this->doctrineHelper->getEntityReference($className, $entityId);
        if (!$entity) {
            return null;
        }

        return $entity;
    }

    /**
     * @param ScrollData $scrollData
     * @param string $html
     * @param string $blockLabel
     */
    protected function addRequestForQuotesBlock(ScrollData $scrollData, $html, $blockLabel)
    {
        $blockId = $scrollData->addBlock($blockLabel);
        $subBlockId = $scrollData->addSubBlock($blockId);
        $scrollData->addSubBlockData($blockId, $subBlockId, $html);
    }

    /**
     * @return string
     */
    abstract protected function getCustomerViewTemplate();

    /**
     * @return string
     */
    abstract protected function getCustomerLabel(): string;

    /**
     * @return string
     */
    abstract protected function getCustomerUserViewTemplate();

    /**
     * @return string
     */
    abstract protected function getCustomerUserLabel(): string;
}
