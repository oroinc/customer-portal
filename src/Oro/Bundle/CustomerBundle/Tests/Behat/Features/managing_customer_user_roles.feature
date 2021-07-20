@ticket-BB-8128
@automatically-ticket-tagged
Feature: Managing customer user roles
  In order to control customer user permissions
  As an Administrator
  I want to be able to manage user roles

  Scenario: Customer user role create
    Given I login as administrator
    And go to Customers/Customer User Roles
    When I click "Create Customer User Role"
    And I fill form with:
      | Role | Test customer user role |
    And select following permissions:
      | Customer Group | View:Full | Create:Full | Edit:Full | Delete:Full |
    And I save and close form
    Then I should see "Test customer user role"

  Scenario: Delete user role
    Given I go to Customers/Customer User Roles
    Then I should see Test customer user role in grid
    And I keep in mind number of records in list
    When I click Delete Test customer user role in grid
    And I confirm deletion
    Then the number of records decreased by 1
    And I should not see "Test customer user role"
