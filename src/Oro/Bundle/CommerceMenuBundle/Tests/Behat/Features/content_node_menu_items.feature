@ticket-BB-21885
@fixture-OroCommerceMenuBundle:content_node_menu_items/customer_user.yml
@fixture-OroCommerceMenuBundle:content_node_menu_items/web_catalog.yml
@fixture-OroCommerceMenuBundle:content_node_menu_items/content_nodes.yml
@fixture-OroCommerceMenuBundle:content_node_menu_items/categories.yml

Feature: Content Node Menu Items

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I proceed as the Admin
    And I login as administrator
    And I set "Default Web Catalog" as default web catalog

  Scenario: Hide system content node menu item
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I click on "Node-1" in tree "Sidebar Menu Tree"
    And I click "Hide"

  Scenario: Check that the hidden content node menu item is not displayed on storefront
    Given I proceed as the Buyer
    When I signed in as AmandaRCole@example.org on the store frontend
    Then I should not see "Node-1" in main menu
    And I should see "Node-2" in main menu

  Scenario: Check form fields
    Given I proceed as the Admin
    When I click on "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I click "Create Menu Item"
    Then should see the following options for "Menu Template" select:
      | Flat menu, up to 2 levels deep |
      | Tree, up to 3 levels deep      |
      | Mega menu, up to 4 levels deep |

  Scenario: Create new content node menu item
    When I fill "Commerce Menu Form" with:
      | Title       | My-Node      |
      | Target Type | Content Node |
    And I expand "Node-1" in tree "Menu Update Content Node Field"
    And I click on "Node-1-1" in tree "Menu Update Content Node Field"
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And I should see "My-Node" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Check that the created content node menu item does not have children
    When I expand "My-Node" in tree "Sidebar Menu Tree"
    Then I should not see "Node-1-1-1" belongs to "My-Node" in tree "Sidebar Menu Tree"

  Scenario: Check that the created content node menu item is displayed in menu on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "Node-2" in main menu
    And I should see "My-Node" in main menu
    And I should not see "Node-1" in main menu
    And I should not see "Node-1 / Node-1-1" in main menu
    And I should not see "My-Node / Node-1-1-1" in main menu

  Scenario: Set "Menu Template"
    Given I proceed as the Admin
    And I click on "My-Node" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Menu Template | Tree, up to 3 levels deep |
    And I save form

  Scenario: Increase "Max Traverse Level" to 3
    And I click on "My-Node" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Max Traverse Level | 3 |
    And I save form

  Scenario: Check that the created content node menu item has children up to 3rd level
    When I expand "My-Node" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1" belongs to "My-Node" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1-1" belongs to "Node-1-1-1" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1-1-1" belongs to "Node-1-1-1-1" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should not see "Node-1-1-1-1-1-1" belongs to "Node-1-1-1-1-1" in tree "Sidebar Menu Tree"

  Scenario: Check that the created content node menu item is displayed in menu using menu template on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "My-Node / Node-1-1-1 / Node-1-1-1-1 / Node-1-1-1-1-1" in main menu

  Scenario: Decrease "Max Traverse Level" of child content node to 0
    Given I proceed as the Admin
    When I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I click on "Node-1-1-1" in tree "Sidebar Menu Tree"
    Then should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
    When I fill "Commerce Menu Form" with:
      | Max Traverse Level | 0 |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check that the child content node menu item has no children
    When I expand "My-Node" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1" belongs to "My-Node" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1-1" in tree "Sidebar Menu Tree"
    Then I should not see "Node-1-1-1-1" belongs to "Node-1-1-1" in tree "Sidebar Menu Tree"

  Scenario: Check that the child content node menu item is displayed without children in menu on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "My-Node / Node-1-1-1" in main menu
    And I should not see "My-Node / Node-1-1-1 / Node-1-1-1-1" in main menu

  Scenario: Check that the label of content node tree menu item can be changed
    Given I proceed as the Admin
    And I click on "Node-1-1-1" in tree "Sidebar Menu Tree"
    When I fill "Commerce Menu Form" with:
      | Title | Node-1-1-1-upd |
    And I save form
    Then I should see "Node-1-1-1-upd" belongs to "My-Node" in tree "Sidebar Menu Tree"

  Scenario: Create custom menu item inside the content node menu items tree
    When I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title  | Custom-1-1-1        |
      | Target | URI                 |
      | URI    | https://example.org |
    And I save form
    Then I should see "Custom-1-1-1" belongs to "Node-1-1-1-upd" in tree "Sidebar Menu Tree"

  Scenario: Check that the content node tree menu item can be moved outside of its parent
    Given I expand "My-Node" in tree "Sidebar Menu Tree"
    When I move "Node-1-1-1-upd" before "Node-2" in tree "Sidebar Menu Tree"
    And I reload the page
    Then I should see "Node-1-1-1-upd" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should see "Custom-1-1-1" belongs to "Node-1-1-1-upd" in tree "Sidebar Menu Tree"

  Scenario: Check max traverse level can be increased to 5
    When I click on "Node-1-1-1-upd" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Max Traverse Level | 5                         |
      | Menu Template      | Tree, up to 3 levels deep |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I expand "Node-1-1-1-upd" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1-1" belongs to "Node-1-1-1-upd" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1-1-1" belongs to "Node-1-1-1-1" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1-1-1-1" belongs to "Node-1-1-1-1-1" in tree "Sidebar Menu Tree"

  Scenario: Check that the content node tree menu item moved outside of its parent is displayed on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "Node-1-1-1-upd" in main menu
    And I should see "Node-1-1-1-upd / Custom-1-1-1" in main menu
    And I should not see "My-Node / Node-1-1-1" in main menu
    And I should not see "My-Node / Node-1-1-1-upd" in main menu

  Scenario: Move content node tree menu item outside of its parent
    Given I proceed as the Admin
    When I expand "Node-1" in tree "Sidebar Menu Tree"
    And I move "Node-1-1" before "About" in tree "Sidebar Menu Tree"
    And I reload the page
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Move content node to root node
    Given go to Marketing/ Web Catalog
    And click "Edit Content Tree" on row "Default Web Catalog" in grid
    And I expand "Node-1" in tree
    When I move "Node-1-1" into "Root-Node" in tree
    And I uncheck "Create 301 Redirect from old to new URLs"
    And I click "Apply" in modal window
    Then I should see "Node-1-1" belongs to "Root-Node" in tree

  Scenario: Change max traverse level of parent to 1
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    When I click on "Node-1" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Max Traverse Level | 1 |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Move content node tree menu item back to its previous parent
    When I expand "Node-1" in tree "Sidebar Menu Tree"
    And I move "Node-1-1" into "Node-1" in tree "Sidebar Menu Tree"
    And I reload the page
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1" belongs to "Node-1" in tree "Sidebar Menu Tree"

  Scenario: Check that the content node menu item can be moved inside another one
    When I move "Node-2" into "My-Node" in tree "Sidebar Menu Tree"
    And I expand "My-Node" in tree "Sidebar Menu Tree"
    And I reload the page
    And I expand "My-Node" in tree "Sidebar Menu Tree"
    Then I should see "Node-2" belongs to "My-Node" in tree "Sidebar Menu Tree"

  Scenario: Check that the system menu item can be moved inside content node tree menu item
    When I expand "My-Node" in tree "Sidebar Menu Tree"
    And I move "About" before "Node-2" in tree "Sidebar Menu Tree"
    And I reload the page
    And I expand "My-Node" in tree "Sidebar Menu Tree"
    Then I should see "About" belongs to "My-Node" in tree "Sidebar Menu Tree"
    And I should see "Node-2" after "About" in tree "Sidebar Menu Tree"
