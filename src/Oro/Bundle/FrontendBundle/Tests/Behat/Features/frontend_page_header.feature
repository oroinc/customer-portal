@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroUserBundle:UserLocalizations.yml
@regression

Feature: Frontend Page Header

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Promotional content block - yes, top navigation menu - yes, language/currency switching - no, quick access button - no, standalone main menu - yes, quick links - yes
    Given I proceed as the Admin
    And I login as administrator
    And I go to System / Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And I fill form with:
      | Enabled Localizations | [English, German Localization, French Localization] |
      | Default Localization  | English (United States)                             |
    And I submit form
    Then I should see "Configuration saved" flash message
    And I follow "Commerce/Catalog/Pricing" on configuration sidebar
    When fill "Pricing Form" with:
      | Enabled Currencies | [US Dollar ($), Euro (€)] |
    And I submit form
    Then I should see "Configuration saved" flash message
    And I follow "Commerce/Design/Theme" on configuration sidebar
    And uncheck "Use default" for "Top Navigation Menu" field
    And uncheck "Use default" for "Language and Currency Switchers" field
    And uncheck "Use default" for "Quick Access Button" field
    And uncheck "Use default" for "Standalone Main Menu" field
    And uncheck "Use default" for "Quick Links" field
    And I fill form with:
      | Top Navigation Menu             | commerce_top_nav         |
      | Language and Currency Switchers | always_in_hamburger_menu |
      | Quick Access Button             | frontend_menu            |
      | Standalone Main Menu            | true                     |
      | Quick Links                     | commerce_quick_access    |
    And I submit form
    Then I should see "Configuration saved" flash message
    Then I proceed as the Buyer
    And I login as AmandaRCole@example.org buyer
    And I should see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should see "CustomerMenu" element with text "Amanda Cole" inside "Header" element
    And I should see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should see "StandaloneMenu" element with text "Resource Library About Contact Us" inside "PageStandaloneMenuContainer" element
    And I should see "PageStandaloneMenuContainer" element with text "Quick Order" inside "Header" element
    And I should see "0 No Shopping Lists"
    Then I set window size to 375x640
    And I should not see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should not see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should not see "Amanda Cole"
    And I should not see "Resource Library About Contact Us"
    And I should not see "Quick Order"
    And I should not see "0 No Shopping Lists"
    Then I click on "Main Menu Button"
    And I should see "Quick Order"
    And I should see "Product"
    And I should see "1-800-555-5555 Contact Us"
    And I set window size to 1920x900

  Scenario: Promotional content block - no, top navigation menu - no, language/currency switching - no, quick access button - yes, standalone main menu - no, quick links - yes
    Given I proceed as the Admin
    And I fill form with:
      | Top Navigation Menu             |                          |
      | Language and Currency Switchers | always_in_hamburger_menu |
      | Quick Access Button             | frontend_menu            |
      | Standalone Main Menu            | false                    |
      | Quick Links                     | commerce_quick_access    |
    And I submit form
    Then I should see "Configuration saved" flash message
    Then I proceed as the Buyer
    And I reload the page
    And I should see "MiddleBarMenus" element with text "Quick Order" inside "Header" element
    And I should see "MiddleBarMenus" element with text "Amanda Cole" inside "Header" element
    And I should see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should not see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should not see "Header" element with text "Resource Library About Contact Us" inside "Header" element
    Then I set window size to 375x640
    And I should not see "MiddleBarMenus" element with text "Quick Order" inside "Header" element
    And I should not see "Amanda Cole"
    And I should not see "MiddleLeftSide" element with text "Product" inside "Header" element
    Then I click on "Main Menu Button"
    And I should see "Quick Order"
    And I should see "Product"
    And I set window size to 1920x900

  Scenario: Promotional content block - yes, top navigation menu - no, language/currency switcher - yes, quick access button - yes, standalone main menu - no, quick links - yes.
    Given I proceed as the Admin
    And uncheck "Use default" for "Quick Access Button Label" field
    And I fill form with:
      | Top Navigation Menu             |                       |
      | Language and Currency Switchers | Above the header      |
      | Quick Access Button             | frontend_menu         |
      | Standalone Main Menu            | false                 |
      | Quick Links                     | commerce_quick_access |
      | Quick Access Button Label       | Test Label            |
    And I submit form
    Then I should see "Configuration saved" flash message
    Then I proceed as the Buyer
    And I reload the page
    And I should see "$(US Dollar) €(Euro)"
    And I should see "English"
    And I should see "MiddleLeftSide" element with text "Test Label" inside "Header" element
    Then I set window size to 375x640
    And I should not see "$(US Dollar) €(Euro)"
    And I should not see "English"

  Scenario: Promotional content block - yes, top navigation menu - yes, language/currency switcher - yes,  quick access button - no, standalone main menu - yes, quick links - yes.
    Given I proceed as the Admin
    And I fill form with:
      | Top Navigation Menu             | commerce_top_nav      |
      | Language and Currency Switchers | Above the header      |
      | Quick Access Button             |                       |
      | Standalone Main Menu            | true                  |
      | Quick Links                     | commerce_quick_access |
      | Quick Access Button Label       | Product               |
    And I submit form
    Then I should see "Configuration saved" flash message
    Then I proceed as the Buyer
    And I reload the page
    And I should see "PageStandaloneMenuContainer" element with text "Quick Order" inside "Header" element
    And I should see "MiddleBarMenus" element with text "Amanda Cole" inside "Header" element
    And I should see "MiddleLeftSide" element with text "Product" inside "Header" element
    And I should see "TopRightBar" element with text "1-800-555-5555 Contact Us" inside "Header" element
    And I should see "Header" element with text "Resource Library About Contact Us" inside "Header" element
    Then I set window size to 375x640
    Then I click on "Search Widget Input"
    And I should see "Cancel"

  Scenario: Search on smaller screens - standalone.
    Given I proceed as the Admin
    And uncheck "Use default" for "Search On Smaller Screens" field
    And I fill form with:
      | Search On Smaller Screens | Standalone |
    And I submit form
    Then I should see "Configuration saved" flash message
    Then I proceed as the Buyer
    And I reload the page
    Then I set window size to 375x640
    Then I click on "Search Widget Input"
    And I should not see "Cancel"
