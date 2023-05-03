@ticket-BB-21885
@fixture-OroCommerceMenuBundle:root_menu_item_target_type__content_node/web_catalog.yml
@fixture-OroCommerceMenuBundle:root_menu_item_target_type__content_node/content_nodes.yml

Feature: Root Menu Item Target Type - Content Node

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I proceed as the Admin
    And I login as administrator
    And I set "Default Web Catalog" as default web catalog

  Scenario: Check that root menu item target type can be changed to None
    Given I go to System/Frontend Menus
    And I click view "commerce_main_menu" in grid
    When I fill "Commerce Menu Form" with:
      | Target Type | None |
    Then I should not see a "Menu Update Category Field" element
    And I should not see a "Menu Update Content Node Field" element
    When I save form
    Then I should not see a "Menu Update Category Field" element
    And I should not see a "Menu Update Content Node Field" element
    And "Commerce Menu Form" must contain values:
      | Target Type | None |
    And I should not see "Node-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should not see "Node-2" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should not see "Node-3" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Check that content node menu items are not displayed on storefront
    Given I proceed as the Buyer
    Then I should not see "Node-1" in main menu
    And I should not see "Node-2" in main menu
    And I should not see "Node-3" in main menu

  Scenario: Change the root menu item target type back to Content Node
    Given I proceed as the Admin
    When I fill "Commerce Menu Form" with:
      | Target Type | Content Node        |
      | Web Catalog | Default Web Catalog |
    And I click on "Node-1" in tree "Menu Update Content Node Field"
    And I save form
    Then "Commerce Menu Form" must contain values:
      | Target Type | Content Node |
    And I should see "Node-1-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should not see "Node-2" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should not see "Node-3" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Check that Node-1 contains Node-1-1 menu items
    When I expand "Node-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1" belongs to "Node-1-1" in tree "Sidebar Menu Tree"
