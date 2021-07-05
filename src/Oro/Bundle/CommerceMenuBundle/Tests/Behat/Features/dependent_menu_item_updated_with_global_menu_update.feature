@regression
@ticket-BB-20696
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Dependent menu item updated with global menu update
  In order to be able to manage menu items
  As an administrator
  I want to have update dependent menus when global menu updated

  Scenario: Create Global menu item
    Given I login as administrator
    When I go to System/Frontend Menus
    And I click view commerce_main_menu in grid
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | Test Item        |
      | Target Type | URI              |
      | URI         | /about           |
      | Description | test description |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Save menu item for Customer
    When I go to Customers/Customers
    And click View first customer in grid
    And I click "Edit Frontend Menu"
    And I click view commerce_main_menu in grid
    And I click "Test Item"
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Update menu item on global level
    When I go to System/Frontend Menus
    And I click view commerce_main_menu in grid
    And I click "Test Item"
    When I fill "Commerce Menu Form" with:
      | URI | /about_updated |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check menu item for Customer
    When I go to Customers/Customers
    And click View first customer in grid
    And I click "Edit Frontend Menu"
    And I click view commerce_main_menu in grid
    And I click "Test Item"
    Then URI field should has "/about_updated" value
