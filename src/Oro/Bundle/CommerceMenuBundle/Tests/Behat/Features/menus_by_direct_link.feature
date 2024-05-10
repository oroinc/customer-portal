@regression
@ticket-BB-22509
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Menus by direct link
  In order to keep system ACL protected
  As an Administrator
  I should be sure that access to the menu edit functionality by direct links are ACL protected

  Scenario: Feature Background
    Given sessions active:
      | Admin  |first_session  |
      | Admin1 |second_session |

  Scenario: View customer menu with default permissions
    Given I proceed as the Admin
    Given I login as administrator
    When I go to Customers/ Customers
    And I click View first customer in grid
    And I click "Edit Frontend Menu"
    Then I should see "Frontend Menus"

  Scenario: Disable manage menu capability
    Given I proceed as the Admin1
    And I login as administrator
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    When I click edit "Administrator" in grid
    And I uncheck "Manage Menus" entity permission
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: View customer menu by direct link without permissions
    Given I proceed as the Admin
    When I reload the page
    Then I should see "403. Forbidden You don't have permission to access this page."

  Scenario: Enable manage menu capability
    Given I proceed as the Admin1
    And I login as administrator
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    When I click edit "Administrator" in grid
    And I check "Manage Menus" entity permission
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: View customer group menu with default permissions
    Given I proceed as the Admin
    Given I login as administrator
    When I go to Customers/ Customer Groups
    And I click View Non-Authenticated Visitors in grid
    And I click "Edit Frontend Menu"
    Then I should see "Frontend Menus"

  Scenario: Disable manage menu capability
    Given I proceed as the Admin1
    And I login as administrator
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    When I click edit "Administrator" in grid
    And I uncheck "Manage Menus" entity permission
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: View customer group menu by direct link without permissions
    Given I proceed as the Admin
    When I reload the page
    Then I should see "403. Forbidden You don't have permission to access this page."

  Scenario: Enable manage menu capability
    Given I proceed as the Admin1
    And I login as administrator
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    When I click edit "Administrator" in grid
    And I check "Manage Menus" entity permission
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: View website menu with default permissions
    Given I proceed as the Admin
    Given I login as administrator
    When I go to System/ Websites
    And I click View Default in grid
    And I click "Edit Frontend Menu"
    Then I should see "Frontend Menus"

  Scenario: Disable manage menu capability
    Given I proceed as the Admin1
    And I login as administrator
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    When I click edit "Administrator" in grid
    And I uncheck "Manage Menus" entity permission
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: View website menu by direct link without permissions
    Given I proceed as the Admin
    When I reload the page
    Then I should see "403. Forbidden You don't have permission to access this page."
