@regression
@ticket-BB-20696
@ticket-BAP-21510
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

  Scenario: View menu item frontend_menu for Customer
    When I go to Customers/Customers
    And click View first customer in grid
    And I click "Edit Frontend Menu"
    And I click view frontend_menu in grid
    Then I should see a "Commerce Menu Form" element

  Scenario: Create menu item frontend_menu for Customer
    When I click "Create Menu Item"
    Then I fill "Commerce Menu Form" with:
      | Title       | Frontend Item           |
      | Target Type | URI                     |
      | URI         | /test_frontend_menu_url |
      | Description | test description        |
    And I save form
    And I should see "Menu item saved successfully." flash message

  Scenario: Create child menu item frontend_menu for Customer
    When I click "Create Menu Item"
    And I fill "Menu Form" with:
      | Title       | Frontend Child Item           |
      | Target Type | URI                           |
      | URI         | /test_frontend_menu_url_child |
      | Description | test description child        |
      | Condition   | true                          |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Update menu item frontend_menu for Customer
    When I click "Frontend Item"
    And I fill "Menu Form" with:
      | Title | Frontend Item Update            |
      | URI   | /test_frontend_menu_url_updated |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And page has "Frontend Item Update" header

  Scenario: Update child menu item frontend_menu for Customer
    When I click "Frontend Child Item"
    And I fill "Menu Form" with:
      | Title | Frontend Child Item Update            |
      | URI   | /test_frontend_menu_url_child_updated |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And page has "Frontend Child Item Update" header

  Scenario: Move menu item frontend_menu for Customer
    When I click "Frontend Child Item Update"
    And I drag and drop "Frontend Child Item Update" before "Frontend Item Update"
    And I should see "The menu has been updated" flash message

  Scenario: View menu item frontend_menu for Customer Groups
    When go to Customers/ Customer Groups
    And I click View Non-Authenticated Visitors in grid
    And I click "Edit Frontend Menu"
    And I click view frontend_menu in grid
    Then I should see a "Commerce Menu Form" element

  Scenario: Create menu item frontend_menu for Customer Groups
    When I click "Create Menu Item"
    Then I fill "Commerce Menu Form" with:
      | Title       | Frontend Item                           |
      | Target Type | URI                                     |
      | URI         | /test_frontend_menu_customer_groups_url |
      | Description | test description                        |
    And I save form
    And I should see "Menu item saved successfully." flash message

  Scenario: Create child menu item frontend_menu for Customer Groups
    When I click "Create Menu Item"
    And I fill "Menu Form" with:
      | Title       | Frontend Child Item                           |
      | Target Type | URI                                           |
      | URI         | /test_frontend_menu_customer_groups_url_child |
      | Description | test description child                        |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Update menu item frontend_menu for Customer Groups
    When I click "Frontend Item"
    And I fill "Menu Form" with:
      | Title | Frontend Item Update                            |
      | URI   | /test_frontend_menu_customer_groups_url_updated |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And page has "Frontend Item Update" header

  Scenario: Update child menu item frontend_menu for Customer Groups
    When I click "Frontend Child Item"
    And I fill "Menu Form" with:
      | Title       | Frontend Child Item Update                            |
      | Description | test description child                                |
      | URI         | /test_frontend_menu_customer_groups_url_child_updated |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And page has "Frontend Child Item Update" header

  Scenario: Move menu item frontend_menu for Customer Groups
    When I click "Frontend Child Item Update"
    And I drag and drop "Frontend Child Item Update" before "Frontend Item Update"
    And I should see "The menu has been updated" flash message
