@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroUserBundle:UserLocalizations.yml
@regression

Feature: Frontend Page Header

  Scenario: Feature background
    Given sessions active:
      | Admin       | first_session  |
      | Buyer       | second_session |
      | user_mobile | mobile_session |

  Scenario: Promotional content block - yes, top navigation menu - no, language/currency switching - no, quick access button - no, standalone main menu - yes, quick links - yes
    Given I proceed as the Admin
    And I login as administrator
    When I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill form with:
      | Enabled Localizations | [English (United States) , German Localization, French Localization] |
      | Default Localization  | English (United States)                                              |
    And I submit form
    Then I should see "Configuration saved" flash message
    When I follow "Commerce/Catalog/Pricing" on configuration sidebar
    And fill "Pricing Form" with:
      | Enabled Currencies | [US Dollar ($), Euro (€)] |
    And I submit form
    Then I should see "Configuration saved" flash message

    And I go to System / Theme Configurations
    When I click Edit "Refreshing Teal [Website: Default]" in grid
    And I fill "Theme Configuration Form" with:
      | Language and Currency Switchers   | always_in_hamburger_menu |
      | Quick Access Button Label         | Product                  |
      | Quick Access Button Type          | Frontend Menu            |
      | Quick Access Button Frontend Menu | frontend_menu            |
      | Standalone Main Menu              | true                     |
    And I save and close form
    Then I should see "Theme Configuration has been saved" flash message

    Given I proceed as the Buyer
    When I login as AmandaRCole@example.org buyer
    And I should see "CustomerMenu" element with text "Amanda Cole" inside "Header" element
    And I should see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should not see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should see "StandaloneMenu" element with text "Resource Library About Contact Us" inside "PageStandaloneMenuContainer" element
    And I should see "PageStandaloneMenuContainer" element with text "Quick Order" inside "Header" element
    And I should see "0 No Shopping Lists"

    Given I proceed as the user_mobile
    When I am on homepage
    And I login as AmandaRCole@example.org buyer
    Then I should not see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should not see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should not see "Amanda Cole"
    And I should not see "Resource Library About Contact Us"
    And I should not see "Quick Order"
    And I should not see "0 No Shopping Lists"
    When I click on "Main Menu Button"
    Then I should see "Quick Order"
    And I should see "Amanda Cole"
    And I should see "Product"
    And I should not see "1-800-555-5555 Contact Us"

  Scenario: Promotional content block - no, top navigation menu - yes, language/currency switching - no, quick access button - yes, standalone main menu - no, quick links - yes
    Given I proceed as the Admin
    When I click Edit "Refreshing Teal [Website: Default]" in grid
    And I fill "Theme Configuration Form" with:
      | Top Navigation Menu               | commerce_top_nav_refreshing_teal      |
      | Language and Currency Switchers   | always_in_hamburger_menu              |
      | Quick Access Button Frontend Menu | frontend_menu                         |
      | Standalone Main Menu              | false                                 |
      | Quick Links Menu                  | commerce_quick_access_refreshing_teal |
    And I save and close form
    Then I should see "Theme Configuration has been saved" flash message

    Given I proceed as the Buyer
    When I reload the page
    Then I should see "MiddleBarMenus" element with text "Quick Order" inside "Header" element
    And I should see "MiddleBarMenus" element with text "Amanda Cole" inside "Header" element
    And I should see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should not see "Header" element with text "Resource Library About Contact Us" inside "Header" element

    Given I proceed as the user_mobile
    When I reload the page
    Then I should not see "MiddleBarMenus" element with text "Quick Order" inside "Header" element
    And I should not see "Amanda Cole"
    And I should not see "MiddleLeftSide" element with text "Product" inside "Header" element
    When I click on "Main Menu Button"
    Then I should see "Quick Order"
    And I should see "Product"
    And I should see "1-800-555-5555 Contact Us"

  Scenario: Promotional content block - yes, top navigation menu - no, language/currency switcher - yes, quick access button - yes, standalone main menu - no, quick links - yes.
    Given I proceed as the Admin
    When I click Edit "Refreshing Teal [Website: Default]" in grid
    And I fill "Theme Configuration Form" with:
      | Top Navigation Menu               |                                       |
      | Language and Currency Switchers   | Above the header                      |
      | Quick Access Button Frontend Menu | frontend_menu                         |
      | Standalone Main Menu              | false                                 |
      | Quick Links Menu                  | commerce_quick_access_refreshing_teal |
      | Quick Access Button Label         | Test Label                            |
    And I save and close form
    Then I should see "Theme Configuration has been saved" flash message

    Given I proceed as the Buyer
    When I reload the page
    Then I should see "$ €"
    And I should see "English"
    And I should see "MiddleLeftSide" element with text "Test Label" inside "Header" element

    Given I proceed as the user_mobile
    When I reload the page
    Then I should not see "$ €"
    And I should not see "English"

  Scenario: Promotional content block - yes, top navigation menu - yes, language/currency switcher - yes,  quick access button - no, standalone main menu - yes, quick links - yes.
    Given I proceed as the Admin
    When I click Edit "Refreshing Teal [Website: Default]" in grid
    And I fill "Theme Configuration Form" with:
      | Top Navigation Menu             | commerce_top_nav_refreshing_teal      |
      | Language and Currency Switchers | Above the header                      |
      | Quick Access Button Type        | None                                  |
      | Standalone Main Menu            | true                                  |
      | Quick Links Menu                | commerce_quick_access_refreshing_teal |
      | Quick Access Button Label       | Product                               |
    And I save and close form
    Then I should see "Theme Configuration has been saved" flash message

    Given I proceed as the Buyer
    When I reload the page
    Then I should see "PageStandaloneMenuContainer" element with text "Quick Order" inside "Header" element
    And I should see "MiddleBarMenus" element with text "Amanda Cole" inside "Header" element
    And I should not see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should see "Header" element with text "Resource Library About Contact Us" inside "Header" element

    Given I proceed as the user_mobile
    When I reload the page
    And I click on "Search Widget Input"
    Then I should see "Cancel"

  Scenario: Search on smaller screens - standalone.
    Given I proceed as the Admin
    When I click Edit "Refreshing Teal [Website: Default]" in grid
    And I fill "Theme Configuration Form" with:
      | Search On Smaller Screens | Standalone |
    And I save and close form
    Then I should see "Theme Configuration has been saved" flash message

    Given I proceed as the user_mobile
    When I reload the page
    Then I click on "Search Widget Input"
    And I should not see "Cancel"
