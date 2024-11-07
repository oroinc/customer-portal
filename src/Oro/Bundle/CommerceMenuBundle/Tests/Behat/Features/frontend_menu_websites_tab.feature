@regression
@ticket-BB-20225
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroCommerceMenuBundle:additional_website/second_website_fixture.yml

Feature: Frontend menu websites tab
  In order to be able to manage menu items for different websites
  As an administrator
  I want to save menu item destination per website

  Scenario: Save menu item for Customer default website
    Given I login as administrator
    When I go to Customers/Customers
    And click View first customer in grid
    And I click "Edit Storefront Menu"
    And I click view frontend_menu in "Storefront Menu By Scope Grid 1"
    And "Commerce Menu Form" must contain values:
      | Target Type | None |
    When I fill "Commerce Menu Form" with:
      | Target Type | Category |
    And I click on "All Products" in tree "Menu Update Category Field"
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Save menu item for Customer additional website
    When I go to Customers/Customers
    And click View first customer in grid
    And I click "Edit Storefront Menu"
    When I click "Website Pro" tab
    And I click view frontend_menu in "Storefront Menu By Scope Grid 2"
    And "Commerce Menu Form" must contain values:
      | Target Type | None |
    When I fill "Commerce Menu Form" with:
      | Target Type | Category |
    And I click on "All Products" in tree "Menu Update Category Field"
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Save menu item for Customer Group default website
    When I go to Customers/Customer Groups
    And click View "Non-Authenticated Visitors" in grid
    And I click "Edit Storefront Menu"
    And I click view frontend_menu in "Storefront Menu By Scope Grid 1"
    And "Commerce Menu Form" must contain values:
      | Target Type | None |
    When I fill "Commerce Menu Form" with:
      | Target Type | Category |
    And I click on "All Products" in tree "Menu Update Category Field"
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Save menu item for Customer Group additional website
    When I go to Customers/Customer Groups
    And click View "Non-Authenticated Visitors" in grid
    And I click "Edit Storefront Menu"
    When I click "Website Pro" tab
    And I click view frontend_menu in "Storefront Menu By Scope Grid 2"
    And "Commerce Menu Form" must contain values:
      | Target Type | None |
    When I fill "Commerce Menu Form" with:
      | Target Type | Category |
    And I click on "All Products" in tree "Menu Update Category Field"
    And I save form
    Then I should see "Menu item saved successfully." flash message
