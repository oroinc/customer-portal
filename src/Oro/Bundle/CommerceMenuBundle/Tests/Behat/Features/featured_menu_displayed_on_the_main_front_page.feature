@regression
@ticket-BB-21165
Feature: Featured menu, displayed on the main front page
  In order to check functionalities of frontend menu like creating, editing and nested level constraints
  As an Administrator
  I should be able to create, edit a menu item without any error and have proper visibility in frontstore

  Scenario: Create different window session
    Given sessions active:
      | Admin     |first_session |
      | Guest     |second_session|

  Scenario: Create new menu item
    Given I proceed as the Admin
    When I login as administrator
    And I go to System/Frontend Menus
    And click view "featured_menu" in grid
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | Test Item               |
      | Target Type | URI                     |
      | URI         | /test_featured_menu_url |
      | Description | test description        |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Use new menu item on frontend
    Given I proceed as the Guest
    When I am on the homepage
    Then I should see "VIEW TEST ITEM" button with attributes:
        | href | /test_featured_menu_url |

  Scenario: Edit already existing menu item
    Given I proceed as the Admin
    When I go to System/Frontend Menus
    And I click view "featured_menu" in grid
    And I click "Test Item"
    When I fill "Commerce Menu Form" with:
      | Title | Featured Item_0 |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Use edited menu item on frontend
    Given I proceed as the Guest
    When I am on the homepage
    Then I should see "VIEW FEATURED ITEM_0" button with attributes:
      | href | /test_featured_menu_url |

  Scenario: Change sequence of menu items
    Given I proceed as the Admin
    When I go to System/Frontend Menus
    And I click view "featured_menu" in grid
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | Featured Item_1        |
      | Target Type | URI                    |
      | URI         | http://www.example.com |
      | Description | test description       |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I expand "featured_menu" in tree
    And I click "Featured Item_1"
    And I drag and drop "Featured Item_1" before "Featured Item_0"
    When I click "Save"
    Then I should see "Featured Item_0" after "Featured Item_1" in tree

  Scenario: Change visibility of menu item for unauthorized users
    Given I click "Featured Item_0"
    When I fill "Commerce Menu Form" with:
      | Condition | is_logged_in() |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check visibility of menu item for unauthorized users
    Given I proceed as the Guest
    When I am on the homepage
    Then I should not see "FEATURED ITEM_0"

  Scenario: Check the functionality of max nested level for a menu under a specified website scope
    Given I proceed as the Admin
    When I go to System/Websites
    And click View Default in grid
    And I click "Edit Frontend Menu"
    And I click view "featured_menu" in grid
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | Test Item Default Website       |
      | Target Type | URI                             |
      | URI         | /test_website_featured_menu_url |
      | Description | test description                |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I click "Test Item Default Website"
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | Test Item level 2                |
      | Target Type | URI                              |
      | URI         | /test2_website_featured_menu_url |
      | Description | test description 2               |
    And I save form
    Then I should see "Item \"Test Item level 2\" cannot be saved. Max nesting level is reached." error message
    When I click "Test Item Default Website"
    And I Create Divider
    Then I should see "Item \"---------------\" cannot be saved. Max nesting level is reached." error message
    When I click "featured_menu"
    Then I should see a "Commerce Menu Form" element
