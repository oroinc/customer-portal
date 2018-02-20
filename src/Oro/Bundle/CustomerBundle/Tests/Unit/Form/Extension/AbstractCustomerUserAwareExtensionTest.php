<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractCustomerUserAwareExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AbstractTypeExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->tokenStorage = $this
            ->createMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
    }

    public function assertOptionsNotChangedForNonCustomerUser()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())
            ->method('getUser');
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));
        /** @var \PHPUnit_Framework_MockObject_MockObject|OptionsResolver $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->never())
            ->method($this->anything());

        $this->extension->configureOptions($resolver);
    }

    /**
     * @param object|null $user
     */
    protected function assertCustomerUserTokenCall($user = null)
    {
        if (!$user) {
            $user = new CustomerUser();
        }
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));
    }
}
