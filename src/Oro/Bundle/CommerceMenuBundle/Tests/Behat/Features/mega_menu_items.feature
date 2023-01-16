@ticket-BB-21885
@fixture-OroCommerceMenuBundle:mega_menu_items/customer_user.yml
@fixture-OroCommerceMenuBundle:mega_menu_items/web_catalog.yml
@fixture-OroCommerceMenuBundle:mega_menu_items/content_nodes.yml
@fixture-OroCommerceMenuBundle:mega_menu_items/categories.yml

Feature: Mega Menu Items

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I proceed as the Admin
    And I login as administrator
    And I set "Default Web Catalog" as default web catalog
    And I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    When I click on "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I click "Create Menu Item"
    Then should see the following options for "Menu Template" select:
      | Flat menu, up to 2 levels deep |
      | Tree, up to 3 levels deep      |
      | Mega menu, up to 4 levels deep |

  Scenario: Set "Menu Template"
    When I click on "Node-1" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Menu Template | Mega menu, up to 4 levels deep |
    Then I save form

  Scenario: Check visibility of menu items
    Given I proceed as the Buyer
    When I signed in as AmandaRCole@example.org on the store frontend
    And I should see "Node-1" in main menu
    And I should see "Node-1 / Node-1-1-1 / Node-1-1-1-1 / Node-1-1-1-1-1" in main menu
    And I should see "Node-1 / Node-1-2" in main menu
    And I should see "Node-1 / Node-1-3 / Node-1-3-1" in main menu
    Then I should see "Node-1 / Node-1-3 / Node-1-3-1-1" in main menu

  Scenario: Create item with long name
    Given I proceed as the Admin
    And I click on "Node-1" in tree "Sidebar Menu Tree"
    When I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | Node with loooooooooooooooong name |
      | Target Type | Content Node                       |
    And I expand "Node-1" in tree "Menu Update Content Node Field"
    And I click on "Node-1-1" in tree "Menu Update Content Node Field"
    And I save form
    And I should see "Menu item saved successfully." flash message
    Then I should see "Node with loooooooooooooooong name" belongs to "Node-1" in tree "Sidebar Menu Tree"

  Scenario: Check tooltip when hover over menu item with long name
    Given I proceed as the Buyer
    And I reload the page
    And I should see "Node-1 / Node with loooooooooooooooong name" in main menu
    When I hover on "NodeWithLongName"
    Then I should see "Node with loooooooooooooooong name" in the "Tooltip" element

  Scenario: Check default openning menu items
    Given I reload the page
    When I hover on "Node1"
    And I should see an "Node1_1Active" element
    And I should not see an "Node1_3Active" element
    And I hover on "Node1_3"
    And I should not see an "Node1_1Active" element
    And I should see an "Node1_3Active" element
    And I click on empty space
    And I hover on "Node1"
    And I should not see an "Node1_1Active" element
    And I should see an "Node1_3Active" element
    And I click on "CloseActiveItem"
    And I hover on "Node1"
    And I should see an "Node1_1Active" element
    Then I should not see an "Node1_3Active" element

  Scenario: Check On Sale promo item
    Given I proceed as the Admin
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    And I click on "Node-1-1" in tree "Sidebar Menu Tree"
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title         | On Sale                        |
      | Target Type   | Content Node                   |
      | Menu Template | Flat menu, up to 2 levels deep |
    And I expand "Node-1" in tree "Menu Update Content Node Field"
    And I click on "Node-1-1" in tree "Menu Update Content Node Field"
    And I click "Choose Image"
    And I fill "Digital Asset Dialog Form" with:
      | File  | cat1.jpg |
      | Title | cat1.jpg |
    And I click "Upload"
    And click on cat1.jpg in grid
    And I save form
    And I proceed as the Buyer
    And I reload the page
    When I hover on "Node1"
    And I should not see an "OnSaleItem_level_3" element
    Then I should see an "OnSaleImage" element

  Scenario: Check On Sale promo item on different levels
    Given I proceed as the Admin
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    And I expand "Node-1-1" in tree "Sidebar Menu Tree"
    When I move "On Sale" before "Node-1" in tree "Sidebar Menu Tree"
    And I proceed as the Buyer
    And I reload the page
    Then I should see an "OnSaleItem_level_1" element
    When I proceed as the Admin
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    And I move "On Sale" before "Node-1-2" in tree "Sidebar Menu Tree"
    And I proceed as the Buyer
    And I reload the page
    And I hover on "Node1"
    Then I should see an "OnSaleItem_level_2" element
    When I proceed as the Admin
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    And I expand "Node-1-1" in tree "Sidebar Menu Tree"
    And I expand "Node-1-1-1" in tree "Sidebar Menu Tree"
    And I move "On Sale" before "Node-1-1-1-1" in tree "Sidebar Menu Tree"
    And I proceed as the Buyer
    And I reload the page
    And I hover on "Node1"
    Then I should see an "OnSaleItem_level_4" element
    When I proceed as the Admin
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    And I expand "Node-1-1" in tree "Sidebar Menu Tree"
    Then I move "On Sale" before "Node-1-1-1" in tree "Sidebar Menu Tree"

  Scenario: Check accessibility via keyboard, dive deep and exit on close button press
    Given I proceed as the Buyer
    And I reload the page
    When I focus on "Node1"
    And I press "ArrowRight" key on "Node1" element
    And I should see "Node2" element focused
    And I press "ArrowLeft" key on "Node2" element
    And I should see "Node1" element focused
    And I press "ArrowDown" key on "Node1" element
    And I should see "Node1_1" element focused
    And I press "ArrowRight" key on "Node1_1" element
    And I should see "Node1_1_Title" element focused
    And I press "ArrowDown" key on "Node1_1_Title" element
    And I should see "Node1_1_1" element focused
    And I press "ArrowDown" key on "Node1_1_1" element
    And I should see "Node1_1_1_1" element focused
    And I press "ArrowUp" key on "Node1_1_1_1" element
    And I should see "Node1_1_1" element focused
    And I press "ArrowUp" key on "Node1_1_1" element
    And I should see "Node1_1_Title" element focused
    And I press "ArrowUp" key on "Node1_1_Title" element
    And I should see "CloseActiveItem" element focused
    And I press "Enter" key on "CloseActiveItem" element
    Then I should see "Node1" element focused

  Scenario: Check accessibility via keyboard, dive deep and exit on press ESC button
    Given I focus on "Node1"
    When I press "ArrowDown" key on "Node1" element
    And I should see "Node1_1" element focused
    And I press "ArrowRight" key on "Node1_1" element
    And I should see "Node1_1_Title" element focused
    And I press "ArrowDown" key on "Node1_1_Title" element
    And I should see "Node1_1_1" element focused
    And I press "ArrowDown" key on "Node1_1_1" element
    And I should see "Node1_1_1_1" element focused
    And I press "Esc" key on "Node1_1_1_1" element
    Then I should see "Node1" element focused

  Scenario: Check accessibility via keyboard, navigation through different menu levels
    Given I focus on "Node1"
    When I press "ArrowDown" key on "Node1" element
    And I should see "Node1_1" element focused
    And I press "ArrowRight" key on "Node1_1" element
    And I should see "Node1_1_Title" element focused
    And I press "ArrowDown" key on "Node1_1_Title" element
    And I should see "Node1_1_1" element focused
    And I press "ArrowLeft" key on "Node1_1_1" element
    And I should see "Node1_1" element focused
    And I press "ArrowDown" key on "Node1_1" element
    And I should see "Node1_2" element focused
    And I press "ArrowRight" key on "Node1_2" element
    Then I should see "Node2" element focused
