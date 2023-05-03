@ticket-BB-21885
@fixture-OroCommerceMenuBundle:master_catalog_menu_items/customer_user.yml
@fixture-OroCommerceMenuBundle:master_catalog_menu_items/categories.yml

Feature: Master Catalog Menu Items

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I proceed as the Admin
    And I login as administrator

  Scenario: Check that 1st level categories from Master Catalog appear in menu as 1st level menu items by default
    Given I go to System/Frontend Menus
    When I click view "commerce_main_menu" in grid
    Then "Commerce Menu Form" must contain values:
      | Target Type        | Category |
      | Max Traverse Level | 6        |
    And I should see "Category-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should see "Category-2" after "Category-1" in tree "Sidebar Menu Tree"
    And I should see "Category-3" after "Category-2" in tree "Sidebar Menu Tree"

  Scenario: Check that category menu items contain sub category menu items
    When I expand "Category-1" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1" belongs to "Category-1" in tree "Sidebar Menu Tree"
    When I expand "Category-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1" belongs to "Category-1-1" in tree "Sidebar Menu Tree"
    When I expand "Category-3" in tree "Sidebar Menu Tree"
    Then I should see "Category-3-1" belongs to "Category-3" in tree "Sidebar Menu Tree"

  Scenario: Check form fields state
    When I click on "Category-1" in tree "Sidebar Menu Tree"
    Then the "Target Type" field should be disabled
    And the "Category" field should be disabled
    And the "Max Traverse Level" field should be enabled
    And "Commerce Menu Form" must contain values:
      | Target Type        | Category                       |
      | Max Traverse Level | 5                              |
      | Menu Template      | Flat menu, up to 2 levels deep |
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |
      | 5 |
    When I click on "Category-1-1" in tree "Sidebar Menu Tree"
    Then the "Target Type" field should be disabled
    And the "Category" field should be disabled
    And the "Max Traverse Level" field should be enabled
    And "Commerce Menu Form" must contain values:
      | Target Type        | Category               |
      | Max Traverse Level | 4                      |
      | Menu Template      | Choose a Menu Template |
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |

  Scenario: Check that category menu items are displayed on storefront
    Given I proceed as the Buyer
    When I signed in as AmandaRCole@example.org on the store frontend
    Then I should see "Category-1" in main menu
    And I should see "Category-1 / Category-1-1" in main menu
    And I should see "Category-2" in main menu
    And I should see "Category-3" in main menu
    And I should see "Category-3 / Category-3-1" in main menu

  Scenario: Create new 1st level category
    Given I proceed as the Admin
    When go to Products/ Master Catalog
    And click "Create Category"
    And fill "Create Category Form" with:
      | Title | Category-new |
    And click "Save"
    Then I should see "Category has been saved" flash message

  Scenario: Check that the new 1st level category appears as menu item automatically
    Given I go to System/Frontend Menus
    When click view "commerce_main_menu" in grid
    Then I should see "Category-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should see "Category-2" after "Category-1" in tree "Sidebar Menu Tree"
    And I should see "Category-3" after "Category-2" in tree "Sidebar Menu Tree"
    And I should see "Category-new" after "Category-3" in tree "Sidebar Menu Tree"

  Scenario: Check that the new category menu item appeared on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "Category-1" in main menu
    And I should see "Category-1 / Category-1-1" in main menu
    And I should see "Category-2" in main menu
    And I should see "Category-3" in main menu
    And I should see "Category-new" in main menu

  Scenario: Check form fields state for new category
    Given I proceed as the Admin
    When I click on "Category-new" in tree "Sidebar Menu Tree"
    Then the "Target Type" field should be disabled
    And the "Category" field should be disabled
    And the "Max Traverse Level" field should be enabled
    And "Commerce Menu Form" must contain values:
      | Target Type        | Category                       |
      | Max Traverse Level | 5                              |
      | Menu Template      | Flat menu, up to 2 levels deep |
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |
      | 5 |

  Scenario: Delete 1st level category
    Given go to Products/ Master Catalog
    When I click on "Category-new" in tree
    And I click "Delete"
    And I click "Yes, Delete" in modal window
    Then I should see "Category deleted" flash message

  Scenario: Check that the new 1st level category disappeared from menu
    Given I go to System/Frontend Menus
    When click view "commerce_main_menu" in grid
    Then I should not see "Category-new" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Check that the menu item of the deleted category disappeared on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should not see "Category-new" in main menu
