@regression
@ticket-BB-27644

Feature: Storefront menu changes are recorded in Data Audit

  Scenario: Feature background
    Given I login as administrator

  Scenario: Adding a menu item is recorded as a creation of that item
    Given I go to System/ Storefront Menus
    And I click view "commerce_main_menu" in grid
    When I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | Audited menu item |
      | Target Type | URI               |
      | URI         | audited-menu-item |
    And I save form
    Then I should see "Menu item saved successfully" flash message
    When I go to System/ Data Audit
    Then I should see following grid containing rows:
      | Entity type             | Entity name                            | Action |
      | Storefront Menu: Global | commerce_main_menu / Audited menu item | Create |
    And I should see "audited-menu-item" in grid

  Scenario: Changing a menu item records only the property that was changed
    Given I go to System/ Storefront Menus
    And I click view "commerce_main_menu" in grid
    When I click on "Audited menu item" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Title | Renamed menu item |
    And I save form
    Then I should see "Menu item saved successfully" flash message
    When I go to System/ Data Audit
    And I filter "Data" as contains "Renamed menu item"
    Then I should see following grid containing rows:
      | Entity type             | Entity name                            | Action |
      | Storefront Menu: Global | commerce_main_menu / Renamed menu item | Update |
    And I should see "Audited menu item" in grid

  Scenario: A change made on the website level is recorded as its own entity type
    Given I go to System/ Websites
    And I click View Default in grid
    And I click "Edit Storefront Menu"
    And I click view "commerce_main_menu" in grid
    When I click on "Renamed menu item" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Title | Website level menu item |
    And I save form
    Then I should see "Menu item saved successfully" flash message
    When I go to System/ Data Audit
    And I filter "Data" as contains "Website level menu item"
    Then I should see following grid containing rows:
      | Entity type              | Entity name                                           | Action |
      | Storefront Menu: Website | commerce_main_menu / Website level menu item (Default) | Update |

  Scenario: The user agent conditions a menu item is shown under are recorded
    Given I go to System/ Storefront Menus
    And I click view "commerce_main_menu" in grid
    When I click on "Renamed menu item" in tree "Sidebar Menu Tree"
    And I click "Add User Agent Condition"
    And I fill "Commerce Menu Form" with:
      | User Agent Contains Value | AuditedAgent |
    And I save form
    Then I should see "Menu item saved successfully" flash message
    When I go to System/ Data Audit
    And I filter "Data" as contains "AuditedAgent"
    Then I should see following grid containing rows:
      | Entity type             | Entity name                            | Action |
      | Storefront Menu: Global | commerce_main_menu / Renamed menu item | Update |
    And I should see "User Agent Conditions" in grid

  Scenario: Deleting a menu item is recorded as its removal
    Given I go to System/ Storefront Menus
    And I click view "commerce_main_menu" in grid
    When I click on "Renamed menu item" in tree "Sidebar Menu Tree"
    And I click "Delete"
    And I click "Yes, Delete" in modal window
    Then I should see "Menu item successfully deleted." flash message
    When I go to System/ Data Audit
    And I filter "Entity name" as contains "Renamed menu item"
    Then I should see following grid containing rows:
      | Entity type             | Entity name                            | Action |
      | Storefront Menu: Global | commerce_main_menu / Renamed menu item | Remove |

  Scenario: Data Audit is filtered by the storefront menu levels
    Given I go to System/ Data Audit
    When I check "Storefront Menu: Global" in "Entity Type" filter
    Then I should see "Storefront Menu: Global" in grid
    When I check "Storefront Menu: Website" in "Entity Type" filter
    And I check "Storefront Menu: Customer Group" in "Entity Type" filter
    And I check "Storefront Menu: Customer" in "Entity Type" filter
    Then the number of records greater than or equal to 1
