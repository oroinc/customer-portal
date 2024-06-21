@regression
@ticket-BB-20225
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroCommerceMenuBundle:second_website_fixture.yml

Feature: Frontend menu websites tab
  In order to be able to manage menu items for different websites
  As an administrator
  I want to save menu item destination per website

  Scenario: Save menu item for Customer default website
    Given I login as administrator
    When I go to Customers/Customers
    And click View first customer in grid
    And I click "Edit Frontend Menu"
    And I click view frontend_menu in "Frontend Menu By Scope Grid 1"
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | All Products         |
      | Target Type | URI                  |
      | URI         | /catalog/allproducts |
    And save form
    Then I should see "Menu item saved successfully" flash message
    And I should see "All Products"

  Scenario: Save menu item for Customer additional website
    When I go to Customers/Customers
    And click View first customer in grid
    And I click "Edit Frontend Menu"
    When I click "Website Pro" tab
    And I click view frontend_menu in "Frontend Menu By Scope Grid 2"
    And I should not see "All Products"
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | Other page |
      | Target Type | URI        |
      | URI         | other-page |
    And save form
    Then I should see "Menu item saved successfully" flash message
    And I should see "Other page"

  Scenario: Save menu item for Customer Group default website
    When I go to Customers/Customer Groups
    And click View "Non-Authenticated Visitors" in grid
    And I click "Edit Frontend Menu"
    And I click view frontend_menu in "Frontend Menu By Scope Grid 1"
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | All Products         |
      | Target Type | URI                  |
      | URI         | /catalog/allproducts |
    And save form
    Then I should see "Menu item saved successfully" flash message
    And  I should see "All Products"

  Scenario: Save menu item for Customer Group additional website
    When I go to Customers/Customer Groups
    And click View "Non-Authenticated Visitors" in grid
    And I click "Edit Frontend Menu"
    When I click "Website Pro" tab
    And I click view frontend_menu in "Frontend Menu By Scope Grid 2"
    And I should not see "All Products"
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | Other page |
      | Target Type | URI        |
      | URI         | other-page |
    And save form
    Then I should see "Menu item saved successfully" flash message
    And  I should see "Other page"
