@regression
@ticket-BB-21885
@fixture-OroCommerceMenuBundle:content_node_menu_items_scopes/customer_user.yml
@fixture-OroCommerceMenuBundle:content_node_menu_items_scopes/web_catalog.yml
@fixture-OroCommerceMenuBundle:content_node_menu_items_scopes/content_nodes.yml

Feature: Content Node Menu Items Scopes

  Scenario: Feature Background
    Given I login as administrator
    And I set "Default Web Catalog" as default web catalog

  Scenario: Move the content node tree menu item outside of its parent in global scope
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I expand "Node-1" in tree "Sidebar Menu Tree"
    When I move "Node-1-1" before "About" in tree "Sidebar Menu Tree"
    And I reload the page
    Then I should see "Node-1-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-1" belongs to "Node-1-1" in tree "Sidebar Menu Tree"

  Scenario: Change max traverse level of parent menu item
    Given I go to System/Websites
    And I click on Default in grid
    And I click "Edit Frontend Menu"
    And I click view commerce_main_menu in grid
    When I click on "Node-1" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Max Traverse Level | 1 |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I expand "Node-1" in tree "Sidebar Menu Tree"
    And I expand "Node-1-2" in tree "Sidebar Menu Tree"
    Then I should not see "Node-1-2-1" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Move the content node tree menu item back to its parent in website scope
    When I move "Node-1-1" before "Node-1-2" in tree "Sidebar Menu Tree"
    And I reload the page
    Then I should see "Node-1-1" belongs to "Node-1" in tree "Sidebar Menu Tree"
    When I expand "Node-1-1" in tree "Sidebar Menu Tree"
    Then I should not see "Node-1-1-1" belongs to "Node-1-1" in tree "Sidebar Menu Tree"
