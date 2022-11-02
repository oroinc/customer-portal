@regression
@ticket-BB-16829
Feature: Manage entity Menu Update
  In order to update "Menu Update" entity
  As an Administrator
  I want to be able to load the page with "Menu Update" entity in Entity Management

  Scenario: Check page "Manage entity" loads without any errors
    Given I login as administrator
    When I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "MenuUpdate"
    And I click view OroCommerceMenuBundle in grid
    Then I should not see "There was an error performing the requested operation" flash message
    And I should see "Create Field"
    When I click "Number of records"
    Then I should be on Frontend Menus page
