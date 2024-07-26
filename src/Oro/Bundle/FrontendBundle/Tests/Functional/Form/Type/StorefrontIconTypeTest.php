<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Form\Type;

use Oro\Bundle\FrontendBundle\Form\Type\StorefrontIconType;
use Oro\Bundle\TestFrameworkBundle\Test\Form\FormAwareTestTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class StorefrontIconTypeTest extends WebTestCase
{
    use FormAwareTestTrait;

    private const ICONS = [
        'add-note' => 'fa-file-o',
        'alert-circle' => 'fa-exclamation-circle',
        'alert-triangle' => 'fa-exclamation-triangle',
        'arrow-down' => 'fa-arrow-down',
        'arrow-left' => 'fa-arrow-left',
        'arrow-right' => 'fa-arrow-right',
        'arrow-up' => 'fa-arrow-up',
        'award' => 'fa-certificate',
        'book-open' => 'fa-book',
        'bookmark-filled' => 'fa-bookmark',
        'bookmark' => 'fa-bookmark-o',
        'briefcase' => 'fa-briefcase',
        'check-circle' => 'fa-check-circle-o',
        'check' => 'fa-check',
        'chevron-down' => 'fa-chevron-down',
        'chevron-left' => 'fa-chevron-left',
        'chevron-right' => 'fa-chevron-right',
        'chevron-up' => 'fa-chevron-up',
        'clock' => 'fa-clock-o',
        'close' => 'fa-close',
        'columns' => 'fa-columns',
        'compact-list' => 'fa-list',
        'copy' => 'fa-copy',
        'credit-card' => 'fa-credit-card',
        'dollar-sign' => 'fa-dollar',
        'download' => 'fa-download',
        'eye-off' => 'fa-eye-slash',
        'eye' => 'fa-eye',
        'file-text' => 'fa-file-text-o',
        'filter' => 'fa-filter',
        'flag' => 'fa-flag',
        'folder' => 'fa-folder',
        'globe' => 'fa-globe',
        'grid' => 'fa-th-large',
        'group' => 'fa-files-o',
        'hamburger-menu' => 'fa-navicon',
        'help-circle' => 'fa-question-circle-o',
        'info-filled' => 'fa-info-circle',
        'link' => 'fa-link',
        'list' => 'fa-list-ul',
        'lock' => 'fa-lock',
        'log-in' => 'fa-sign-in',
        'log-out' => 'fa-sign-out',
        'map-pin' => 'fa-map-marker',
        'map' => 'fa-map-o',
        'minus-circle' => 'fa-minus-circle',
        'minus' => 'fa-minus',
        'more-horizontal' => 'fa-ellipsis-h',
        'move' => 'fa-level-up',
        'package' => 'fa-cube',
        'pencil' => 'fa-pencil',
        'phone' => 'fa-phone',
        'plus' => 'fa-plus',
        'printer' => 'fa-print',
        'refresh' => 'fa-refresh',
        'remove-note' => 'fa-remove',
        'search' => 'fa-search',
        'settings' => 'fa-cog',
        'shopping-cart' => 'fa-shopping-cart',
        'sliders-applied' => 'fa-sliders',
        'sorting' => 'fa-sort',
        'star' => 'fa-star-o',
        'tag' => 'fa-tag',
        'trash' => 'fa-trash-o',
        'trending-up' => 'fa-line-chart',
        'truck' => 'fa-truck',
        'undo' => 'fa-undo',
        'unlock' => 'fa-unlock',
        'upload' => 'fa-upload',
        'user' => 'fa-user-o',
        'users' => 'fa-users',
        'zap' => 'fa-flash',
        'zoom-in' => 'fa-search-plus',
        'zoom-out' => 'fa-search-minus',
    ];

    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testFormContainsStorefrontIcons(): void
    {
        $formFactory = self::getContainer()->get('form.factory');
        $form = $formFactory->create(StorefrontIconType::class, '', ['csrf_protection' => false]);

        self::assertFormOptions(
            $form,
            [
                'choices' => self::ICONS,
                'placeholder' => '',
                'configs' => [
                    'placeholder' => 'oro.form.choose_value',
                    'result_template_twig' => '@OroForm/Autocomplete/icon/result.html.twig',
                    'selection_template_twig' => '@OroForm/Autocomplete/icon/selection.html.twig',
                ],
            ]
        );
    }

    public function testFormViewContainsSelect2Configs(): void
    {
        $formFactory = self::getContainer()->get('form.factory');
        $form = $formFactory->create(StorefrontIconType::class, '', ['csrf_protection' => false]);

        $formView = $form->createView();

        self::assertArrayIntersectEquals([
            'configs' => [
                'placeholder' => 'oro.form.choose_value',
                'result_template_twig' => '@OroForm/Autocomplete/icon/result.html.twig',
                'selection_template_twig' => '@OroForm/Autocomplete/icon/selection.html.twig',
            ],
        ], $formView->vars);
    }
}
