<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Symfony\Component\Form\FormInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\AutoLoginListener;
use Oro\Bundle\CustomerBundle\Manager\LoginManager;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;

class AutoLoginListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AutoLoginListener */
    private $listener;

    /** @var LoginManager|\PHPUnit_Framework_MockObject_MockObject */
    private $loginManager;

    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject */
    private $configManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->loginManager = $this->createMock(LoginManager::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->listener = new AutoLoginListener($this->loginManager, $this->configManager);
    }

    /**
     * @dataProvider notValidAutoLoginDataProvider
     *
     * @param bool $confirmed
     * @param bool $isEnabledAutoLogin
     */
    public function testAfterFlushWithoutAutoLogin($confirmed, $isEnabledAutoLogin)
    {
        $form = $this->createMock(FormInterface::class);
        $customerUser = new CustomerUser();
        $customerUser->setConfirmed($confirmed);
        $event = new AfterFormProcessEvent($form, $customerUser);
        $this->configManager
            ->expects($this->any())
            ->method('get')
            ->with('oro_customer.auto_login_after_registration')
            ->willReturn($isEnabledAutoLogin);
        $this->loginManager
            ->expects($this->never())
            ->method('logInUser');
        $this->listener->afterFlush($event);
    }

    public function testAfterFlushWithAutoLogin()
    {
        $form = $this->createMock(FormInterface::class);
        $customerUser = new CustomerUser();
        $customerUser->setConfirmed(true);
        $event = new AfterFormProcessEvent($form, $customerUser);
        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with('oro_customer.auto_login_after_registration')
            ->willReturn(true);
        $this->loginManager
            ->expects($this->once())
            ->method('logInUser')
            ->with('frontend_secure', $customerUser);
        $this->listener->afterFlush($event);
    }

    /**
     * @return array
     */
    public function notValidAutoLoginDataProvider()
    {
        return [
            'user not confirmed and auto login enabled' => [
                'confirmed' => false,
                'isEnabledAutoLogin' => true,
            ],
            'user confirmed and auto login disabled' => [
                'confirmed' => true,
                'isEnabledAutoLogin' => false,
            ],
            'user not confirmed and auto login disabled' => [
                'confirmed' => false,
                'isEnabledAutoLogin' => false,
            ],
        ];
    }
}
