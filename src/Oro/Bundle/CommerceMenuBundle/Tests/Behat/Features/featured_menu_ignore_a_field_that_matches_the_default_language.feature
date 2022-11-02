@fixture-OroLocaleBundle:LocalizationFixture.yml
@fixture-OroCommerceMenuBundle:menu_item_translations.yml
Feature: Featured menu ignore a field that matches the default language
  In order to be able to manage menu items
  As an administrator
  I want see predefined values for title fields regardless of the default localization

  Scenario: Feature Background
    Given I enable the existing localizations

  Scenario: Verify localization field
    Given I login as administrator
    When I go to System/Frontend Menus
    And I click view commerce_footer_links in grid
    And I click Information in menu tree
    And I click "Commerce Menu Form Title Fallbacks"
    Then "Commerce Menu Form" must contains values:
      | Title                     | Information       |
      | Title First               | Information       |
      | Title First Use Fallback  | true              |
      | Title First Fallback      | Default Value     |
      | Title Second              | Information       |
      | Title Second Use Fallback | true              |
      | Title Second Fallback     | Default Value     |
      | Title Third               | Information LANG2 |
      | Title Third Use Fallback  | false             |
      | Title Fourth              | Information       |
      | Title Fourth Use Fallback | true              |
      | Title Fourth Fallback     | Default Value     |
      | Title Fifth               | Information LANG2 |
      | Title Fifth Use Fallback  | false             |
