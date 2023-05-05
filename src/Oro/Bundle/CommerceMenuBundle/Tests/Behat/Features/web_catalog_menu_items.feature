@ticket-BB-21885
@fixture-OroCommerceMenuBundle:web_catalog_menu_items/customer_user.yml
@fixture-OroCommerceMenuBundle:web_catalog_menu_items/web_catalog.yml
@fixture-OroCommerceMenuBundle:web_catalog_menu_items/content_nodes.yml
@fixture-OroCommerceMenuBundle:web_catalog_menu_items/categories.yml

Feature: Web Catalog Menu Items

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I proceed as the Admin
    And I login as administrator
    And I set "Default Web Catalog" as default web catalog

  Scenario: Check that 1st level content nodes from Web Catalog appear in menu as 1st level menu items by default
    Given I go to System/Frontend Menus
    When click view "commerce_main_menu" in grid
    Then "Commerce Menu Form" must contain values:
      | Target Type        | Content Node |
      | Max Traverse Level | 6            |
    And I should see "Node-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should see "Node-2" after "Node-1" in tree "Sidebar Menu Tree"
    And I should see "Node-3" after "Node-2" in tree "Sidebar Menu Tree"
    And I should not see "Category-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Check that content node menu items contain sub content node menu items
    When I expand "Node-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1" belongs to "Node-1" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1" belongs to "Node-1-1" in tree "Sidebar Menu Tree"
    When I expand "Node-3" in tree "Sidebar Menu Tree"
    Then I should see "Node-3-1" belongs to "Node-3" in tree "Sidebar Menu Tree"

  Scenario: Check form fields state
    When I click on "Node-1" in tree "Sidebar Menu Tree"
    Then the "Target Type" field should be disabled
    And the "Content Node" field should be disabled
    And the "Max Traverse Level" field should be enabled
    And "Commerce Menu Form" must contain values:
      | Target Type        | Content Node                   |
      | Max Traverse Level | 5                              |
      | Menu Template      | Flat menu, up to 2 levels deep |
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |
      | 5 |
    When I click on "Node-1-1" in tree "Sidebar Menu Tree"
    Then the "Target Type" field should be disabled
    And the "Content Node" field should be disabled
    And the "Max Traverse Level" field should be enabled
    And "Commerce Menu Form" must contain values:
      | Target Type        | Content Node           |
      | Max Traverse Level | 4                      |
      | Menu Template      | Choose a Menu Template |
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |

  Scenario: Check that content node menu items are displayed on storefront
    Given I proceed as the Buyer
    When I signed in as AmandaRCole@example.org on the store frontend
    Then I should see "Node-1" in main menu
    And I should see "Node-1 / Node-1-1" in main menu
    And I should see "Node-2" in main menu
    And I should see "Node-3" in main menu
    And I should see "Node-3 / Node-3-1" in main menu
    And I should not see "Category-1" in main menu

  Scenario: Create new 1st level content node
    Given I proceed as the Admin
    When go to Marketing/ Web Catalog
    And click "Edit Content Tree" on row "Default Web Catalog" in grid
    And I click "Root-Node"
    And click "Create Content Node"
    And I fill "Content Node Form" with:
      | Titles   | Node-new |
      | Url Slug | node-new |
    And I click on "Show Variants Dropdown"
    And I click "Add Category"
    And click "Category-1"
    And I save form
    Then I should see "Content Node has been saved" flash message

  Scenario: Check that the new 1st level content node appears as menu item automatically
    Given I go to System/Frontend Menus
    When click view "commerce_main_menu" in grid
    Then I should see "Node-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should see "Node-2" after "Node-1" in tree "Sidebar Menu Tree"
    And I should see "Node-3" after "Node-2" in tree "Sidebar Menu Tree"
    And I should see "Node-new" after "Node-3" in tree "Sidebar Menu Tree"

  Scenario: Check that the new content node menu item appeared on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "Node-1" in main menu
    And I should see "Node-1 / Node-1-1" in main menu
    And I should see "Node-2" in main menu
    And I should see "Node-3" in main menu
    And I should see "Node-new" in main menu

  Scenario: Check form fields state for new content node
    Given I proceed as the Admin
    When I click on "Node-new" in tree "Sidebar Menu Tree"
    Then the "Target Type" field should be disabled
    And the "Content Node" field should be disabled
    And the "Max Traverse Level" field should be enabled
    And "Commerce Menu Form" must contain values:
      | Target Type        | Content Node                   |
      | Max Traverse Level | 5                              |
      | Menu Template      | Flat menu, up to 2 levels deep |
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |
      | 5 |

  Scenario: Delete 1st level content node
    Given I proceed as the Admin
    When go to Marketing/ Web Catalog
    And click "Edit Content Tree" on row "Default Web Catalog" in grid
    And I click on "Node-new" in tree
    And I click "Delete"
    And I click "Yes, Delete" in modal window
    Then I should see "Content node deleted" flash message

  Scenario: Check that the new 1st level content node disappeared from menu
    Given I go to System/Frontend Menus
    When click view "commerce_main_menu" in grid
    Then I should not see "Node-new" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Check that the menu item of the deleted content node disappeared on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should not see "Node-new" in main menu
