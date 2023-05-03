@regression
@ticket-BB-21885
@fixture-OroCommerceMenuBundle:system_configuration__main_navigation_menu/customer_user.yml

Feature: System Configuration - Main Navigation Menu

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I proceed as the Admin
    And I login as administrator

  Scenario: Change main navigation menu in global system config
    Given I go to System/ Configuration
    When I follow "System Configuration/Websites/Routing" on configuration sidebar
    And uncheck "Use default" for "Main Navigation Menu" field
    And I fill form with:
      | Main Navigation Menu | frontend_menu |
    And I click "Save settings"
    Then I should see "Configuration saved" flash message

  Scenario: Check that the chosen menu is displayed on storefront
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should see "My Account" in main menu
    And I should see "Catalog" in main menu
    And I should not see "Contact Us" in main menu
    And I should not see "About" in main menu

  Scenario: Change main navigation menu for the customer group scope
    Given I proceed as the Admin
    And I go to Customers/Customer Groups
    And I click Configuration All Customers in grid
    When I follow "System Configuration/Websites/Routing" on configuration sidebar
    And uncheck "Use Website" for "Main Navigation Menu" field
    And I fill form with:
      | Main Navigation Menu | commerce_main_menu |
    And I click "Save settings"
    Then I should see "Configuration saved" flash message

  Scenario: Check that the chosen menu for the customer group scope is displayed on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "My Account" in main menu
    And I should see "Catalog" in main menu
    When I signed in as AmandaRCole@example.org on the store frontend
    Then I should see "Contact Us" in main menu
    And I should see "About" in main menu
    And I should not see "My Account" in main menu
    And I should not see "Catalog" in main menu

  Scenario: Change main navigation menu for the customer scope
    Given I proceed as the Admin
    And I go to Customers/Customer
    And I click Configuration Customer-1 in grid
    When I follow "System Configuration/Websites/Routing" on configuration sidebar
    And uncheck "Use Customer Group" for "Main Navigation Menu" field
    And I fill form with:
      | Main Navigation Menu | commerce_top_nav |
    And I click "Save settings"
    Then I should see "Configuration saved" flash message

  Scenario: Check that the chosen menu for the customer scope is displayed on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "Live Chat" in main menu
    And I should not see "Contact Us" in main menu
    And I should not see "About" in main menu
    And I should not see "My Account" in main menu
    And I should not see "Catalog" in main menu
