@ticket-BB-21885
@fixture-OroCommerceMenuBundle:target_type_content_node/customer_user.yml
@fixture-OroCommerceMenuBundle:target_type_content_node/web_catalog.yml
@fixture-OroCommerceMenuBundle:target_type_content_node/scope.yml
@fixture-OroCommerceMenuBundle:target_type_content_node/content_nodes.yml

Feature: Target Type Content Node

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Create menu item with content node target type
    Given I proceed as the Admin
    And I login as administrator
    And I set "Default Web Catalog" as default web catalog
    When I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I click "Create Menu Item"
    Then the "Target Type" field should be enabled
    When I fill "Commerce Menu Form" with:
      | Title       | ContentNodeTarget   |
      | Target Type | Content Node        |
      | Web Catalog | Default Web Catalog |
    And the "Content Node" field should be enabled
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |
      | 5 |
    And I click on "JS Tree item" with title "Clearance" in element "Menu Update Content Node Field"
    And I should not see "Please choose a Web Catalog"
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check menu items on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should see "ContentNodeTarget" button with attributes:
      | href | /clearance |

  Scenario: Restrict content node
    Given I proceed as the Admin
    When I go to Marketing / Web Catalogs
    And I click view Default Web Catalog in grid
    And I click "Edit Content Tree"
    And I click "Clearance"
    And I uncheck "Inherit Parent" element
    And I fill "Content Node Form" with:
      | Content Node Restrictions Customer | Company A |
    And I click "Save"
    Then I should see "Content Node has been saved" flash message

  Scenario: Check content node menu item is hidden on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should not see "ContentNodeTarget"
