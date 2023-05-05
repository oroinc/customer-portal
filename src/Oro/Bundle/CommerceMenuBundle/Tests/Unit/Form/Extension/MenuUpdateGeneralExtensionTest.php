<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Oro\Bundle\CommerceMenuBundle\Form\Extension\MenuUpdateGeneralExtension;
use Oro\Bundle\CommerceMenuBundle\Provider\MenuTemplatesProvider;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\ImageTypeStub;
use Oro\Bundle\CommerceMenuBundle\Tests\Unit\Form\Type\Stub\MenuUpdateTypeStub;
use Oro\Bundle\FormBundle\Form\Type\LinkTargetType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\SecurityBundle\Util\UriSecurityHelper;
use Oro\Bundle\SecurityBundle\Validator\Constraints\NotDangerousProtocolValidator;
use Oro\Bundle\TranslationBundle\Form\Extension\TranslatableChoiceTypeExtension;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class MenuUpdateGeneralExtensionTest extends FormIntegrationTestCase
{
    use MenuItemTestTrait;

    protected function getExtensions(): array
    {
        $menuTemplatesProvider = $this->createMock(MenuTemplatesProvider::class);
        $menuTemplatesProvider->expects(self::any())
            ->method('getMenuTemplates')
            ->willReturn([
                'list' => [
                    'label' => 'List Template',
                ],
                'tree' => [
                    'label' => 'Tree Template',
                ],
            ]);

        return [
            new PreloadedExtension(
                [
                    new LinkTargetType(),
                    ImageType::class => new ImageTypeStub(),
                ],
                [
                    MenuUpdateTypeStub::class => [
                        new MenuUpdateGeneralExtension($menuTemplatesProvider),
                    ],
                    FormType::class => [new TooltipFormExtensionStub($this)],
                    ChoiceType::class => [
                        new TranslatableChoiceTypeExtension(),
                    ],
                ]
            ),
            $this->getValidatorExtension(true),
        ];
    }

    protected function getValidators(): array
    {
        return [
            'oro_security.validator.constraints.not_dangerous_protocol' =>
                new NotDangerousProtocolValidator(new UriSecurityHelper([])),
        ];
    }

    public function testFormHasImageField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertTrue($form->has('image'));
    }

    public function testSubmitImageField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        $image = new File();

        $form->submit(['image' => $image]);

        self::assertEquals($image, $form->getData()->getImage());
    }

    public function testFormHasLinkTargetField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertTrue($form->has('linkTarget'));
    }

    public function testSubmitLinkTargetField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        $linkTarget = LinkTargetType::NEW_WINDOW_VALUE;
        $form->submit(['linkTarget' => $linkTarget]);

        self::assertEquals($linkTarget, $form->getData()->getLinkTarget());
    }

    public function testFormHasMenuTemplateField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        self::assertTrue($form->has('menuTemplate'));
        $this->assertFormOptionEqual(
            ['List Template' => 'list', 'Tree Template' => 'tree'],
            'choices',
            $form->get('menuTemplate')
        );
    }

    public function testSubmitMenuTemplateField(): void
    {
        $menu = $this->createItem('sample_menu');
        $menuUpdate = new MenuUpdateStub();

        $form = $this->factory->create(
            MenuUpdateTypeStub::class,
            $menuUpdate,
            ['menu' => $menu]
        );

        $menuTemplate = 'list';
        $form->submit(['menuTemplate' => $menuTemplate]);

        self::assertEquals($menuTemplate, $form->getData()->getMenuTemplate());
    }
}
