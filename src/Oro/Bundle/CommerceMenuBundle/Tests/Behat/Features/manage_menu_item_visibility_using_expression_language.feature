@regression
@ticket-BB-24083
@fixture-OroCustomerBundle:CustomerUserAmandaRCole.yml

Feature: Manage Menu Item visibility using expression language

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Add new menu item to Commerce Main Menu
    Given I proceed as the Admin
    And login as administrator
    And go to System/ Frontend Menus
    And click view "commerce_main_menu" in grid
    When I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | System level menu item |
      | Target Type | URI                    |
      | URI         | system-level-menu-item |
      | Condition   | !is_logged_in()        |
    And save form
    Then I should see "Menu item saved successfully" flash message

  Scenario: Check that Frontend Menu Items on storefront
    Given I proceed as the Buyer
    And go to the homepage
    Then I should see "System level menu item" in main menu

  Scenario: Change menu item on website level
    Given I proceed as the Admin
    And go to System/ Websites
    And click View Default in grid
    And click "Edit Frontend Menu"
    And click view "commerce_main_menu" in grid
    When I click on "System level menu item" in tree "Sidebar Menu Tree"
    And fill "Commerce Menu Form" with:
      | Title       | Website level menu item |
      | Target Type | URI                     |
      | URI         | website-level-menu-item |
    And save form
    Then I should see "Menu item saved successfully" flash message

  Scenario: Check that Frontend Menu Items on storefront
    Given I proceed as the Buyer
    And I reload the page
    Then I should see "Website level menu item" in main menu

  Scenario: Check that Frontend Menu Items on storefront from Amanda
    Given I go to the homepage
    And I signed in as AmandaRCole@example.org on the store frontend
    Then I should not see "Website level menu item" in main menu


