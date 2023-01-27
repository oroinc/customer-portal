@regression
@ticket-BB-21885
@fixture-OroCommerceMenuBundle:content_node_menu_items_title/customer_user.yml
@fixture-OroCommerceMenuBundle:content_node_menu_items_title/web_catalog.yml
@fixture-OroCommerceMenuBundle:content_node_menu_items_title/content_nodes.yml

Feature: Content Node Menu Items Title

  Scenario: Feature Background
    Given I login as administrator
    And I set "Default Web Catalog" as default web catalog

  Scenario: Update content node title
    When go to Marketing/ Web Catalog
    And click "Edit Content Tree" on row "Default Web Catalog" in grid
    And I expand "Node-1" in tree
    And I click on "Node-1-1" in tree
    And fill "Content Node Form" with:
      | Titles   | Node-1-1-changed |
      | Url Slug | node-1-1-changed |
    And click "Save"
    And I uncheck "Create 301 Redirect from old to new URLs"
    And I click "Apply" in modal window
    Then I should see "Content Node has been saved" flash message

  Scenario: Check that the title of menu item follows content node title
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    When I expand "Node-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-changed" belongs to "Node-1" in tree
    And I should not see "Node-1-1" belongs to "Node-1" in tree

  Scenario: Update content node menu item title
    When I click on "Node-1-1-changed" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Title | Node-1-1-changed-frozen |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Update content node title again
    Given go to Marketing/ Web Catalog
    When click "Edit Content Tree" on row "Default Web Catalog" in grid
    And I expand "Node-1" in tree
    And I click on "Node-1-1-changed" in tree
    And fill "Content Node Form" with:
      | Titles   | Node-1-1-changed-2 |
      | Url Slug | node-1-1-changed-2 |
    And click "Save"
    And I uncheck "Create 301 Redirect from old to new URLs"
    And I click "Apply" in modal window
    Then I should see "Content Node has been saved" flash message

  Scenario: Check that the title of menu item does not follow the content node title anymore
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    When I expand "Node-1" in tree "Sidebar Menu Tree"
    Then I should see "Node-1-1-changed-frozen" belongs to "Node-1" in tree
    And I should not see "Node-1-1-changed" belongs to "Node-1" in tree
