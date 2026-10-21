@regression
@ticket-BB-21959

Feature: Hide custom menu item

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Create custom menu item
    Given I proceed as the Admin
    And I login as administrator
    When I go to System/Storefront Menus
    And click view "commerce_main_menu" in grid
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | Custom Item  |
      | Target Type | URI          |
      | URI         | /custom-item |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And I should see "Delete"
    And I should see "Hide"

  Scenario: Hide custom menu item
    When I click "Hide"
    Then I should see "Menu item is hidden now." flash message
    And I should see "Delete"
    And I should see "Show"

  Scenario: Check that the hidden custom menu item is not displayed on storefront
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should not see "Custom Item" in main menu

  Scenario: Show custom menu item
    Given I proceed as the Admin
    When I click "Show"
    Then I should see "Menu item is visible now." flash message
    And I should see "Hide"

  Scenario: Check that the custom menu item is displayed on storefront again
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should see "Custom Item" in main menu
