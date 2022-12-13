@ticket-BB-18343
@fixture-OroCommerceMenuBundle:menu_page_target.yml
@fixture-OroCommerceMenuBundle:menu_page_target_web_catalog_nodes.yml

Feature: Menu Page Target
  In order to have configurable targets of menu items on store front
  As an administrator
  I want to be able to select page target for menu item on store front
  As a Buyer
  I want to have fully workable menu on store front

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I set "Default Web Catalog" as default web catalog

  Scenario: Create menu items
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | UriTarget   |
      | Target Type | URI         |
      | URI         | /uri-target |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And I click "commerce_main_menu"
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | SystemPageTarget |
      | Target Type | System Page      |
      | System Page | Contact Us       |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And I click "commerce_main_menu"
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | ContentNodeTarget   |
      | Target Type | Content Node        |
      | Web Catalog | Default Web Catalog |
    And I click on "JS Tree item" with title "Clearance" in element "Menu Item Content Node Field"
    And I should not see "Please choose a Web Catalog"
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check menu items on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should see "UriTarget" button with attributes:
      | href | /uri-target |
    And I should see "SystemPageTarget" button with attributes:
      | href | /contact-us |
    And I should see "ContentNodeTarget" button with attributes:
      | href | /clearance |

  Scenario: Restrict content node
    Given I proceed as the Admin
    And I go to Marketing / Web Catalogs
    And I click view Default Web Catalog in grid
    And I click "Edit Content Tree"
    And I click "Clearance"
    Given I uncheck "Inherit Parent" element
    And I fill "Content Node Form" with:
      | Content Node Restrictions Customer | Company A |
    When I click "Save"
    Then I should see "Content Node has been saved" flash message

  Scenario: Check content node menu item is hidden on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should not see "ContentNodeTarget"
