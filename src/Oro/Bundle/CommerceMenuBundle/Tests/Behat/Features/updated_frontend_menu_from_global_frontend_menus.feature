@ticket-BAP-21510
Feature: Updated frontend menu from global frontend menus
  In order to be able to manage menu items
  As an administrator
  I want to have to update dependent menus

  Scenario: View menu item frontend_menu
    When I login as administrator
    And I go to System/Frontend Menus
    And click view "frontend_menu" in grid
    Then I should see a "Commerce Menu Form" element

  Scenario: Create menu item frontend_menu
    When I click "Create Menu Item"
    Then I fill "Commerce Menu Form" with:
      | Title       | Frontend Item           |
      | Target Type | URI                     |
      | URI         | /test_frontend_menu_url |
      | Description | test description        |
    And I save form
    And I should see "Menu item saved successfully." flash message

  Scenario: Create child menu item
    When I click "Create Menu Item"
    And I fill "Menu Form" with:
      | Title       | Frontend Child Item           |
      | Target Type | URI                           |
      | URI         | /test_frontend_menu_url_child |
      | Description | test description child        |
      | Condition   | true                          |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Update menu item
    When I click "Frontend Item"
    And I fill "Menu Form" with:
      | Title | Frontend Item Update            |
      | URI   | /test_frontend_menu_url_updated |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And page has "Frontend Item Update" header

  Scenario: Check update not custom menu item
    When I click "My Account"
    And I should see "Menus"
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And page has "My Account" header

  Scenario: Move menu item
    When I click "Frontend Child Item"
    And I drag and drop "Frontend Child Item" before "Frontend Item Update"
    And I should see "The menu has been updated" flash message

  Scenario: Check creation in not existing menu
    When I go to "admin/menu/frontend/global/not_existing_menu"
    And I should see "404. Not Found"
