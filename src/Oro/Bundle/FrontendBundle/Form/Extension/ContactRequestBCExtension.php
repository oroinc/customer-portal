<?php

namespace Oro\Bundle\FrontendBundle\Form\Extension;

use Oro\Bridge\ContactUs\Form\Type\ContactRequestType;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Decide which is the correct field (organizationName or customerName) in the contact-us form,
 * depending on the theme. This is a BC layer for the contact-us form.
 */
class ContactRequestBCExtension extends AbstractTypeExtension
{
    private CurrentThemeProvider $currentThemeProvider;
    private ThemeManager $themeManager;

    public function __construct(CurrentThemeProvider $currentThemeProvider, ThemeManager $themeManager)
    {
        $this->currentThemeProvider = $currentThemeProvider;
        $this->themeManager = $themeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$builder->has('customerName')) {
            return;
        }
        if (!$this->themeManager->themeHasParent(
            $this->currentThemeProvider->getCurrentThemeId(),
            ['default_50', 'default_51']
        )) {
            return;
        }

        $builder->remove('customerName');
        $builder->add(
            'organizationName',
            TextType::class,
            [
                'required' => false,
                'label' => 'oro.contactus.contactrequest.customer_name.label',
                'property_path' => 'customerName'
            ]
        );
    }

    public static function getExtendedTypes(): array
    {
        return [ContactRequestType::class];
    }
}
