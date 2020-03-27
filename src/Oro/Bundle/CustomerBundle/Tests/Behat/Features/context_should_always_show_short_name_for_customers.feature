@regression
@ticket-BB-18711
@fixture-OroCustomerBundle:CustomerContext.yml

Feature: Context should always show short name for customers
  Scenario: Prepare string field and enable Tasks for Customer entity
    Given I login as administrator
    And I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "Customer"
    And I click edit Customer in grid
    And I fill form with:
      | Tasks       | true |
      # Need to set shorter description since the one in the demo data doesn't pass validation
      | Description | Customers are companies who buy products using OroCommerce store frontend. |
    When I save and close form
    Then I should see "Entity saved" flash message
    And I click "Create field"
    And I fill form with:
      | Field name   | TableColumnStringField |
      | Storage type | Table column           |
      | Type         | String                 |
    And I click "Continue"
    And I save and close form
    Then I should see "Field saved" flash message
    And I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "Account"
    And I click view Account in grid
    And I click "Create field"
    And I fill form with:
      | Field name   | TableColumnStringField |
      | Storage type | Table column           |
      | Type         | String                 |
    And I click "Continue"
    And I save and close form
    Then I should see "Field saved" flash message
    When I click update schema
    Then I should see "Schema updated" flash message
    And I go to Customers/ Customers
    And I click edit Company A in grid
    And I fill form with:
      | TableColumnStringField | Test string |
    And I save and close form
    And I go to Customers/ Accounts
    And I click edit Customer 1 in grid
    And I fill form with:
      | Owner                  | John Doe    |
      | TableColumnStringField | Test string |
    And I save and close form

  Scenario: Check short name is used as context for Customer in tasks
    Given I go to Customers/ Customers
    And I click view Company A in grid
    And I click "More actions"
    And I click "Add task"
    And I fill form with:
      | Subject | Customer task |
    And I click "Create Task"
    And I go to Activities/ Tasks
    Then I should see "Company A"
    And I should not see "Test string Company A"

  Scenario: Check short name is used as context for Account in tasks
    Given I go to Customers/ Accounts
    And I click view Customer 1 in grid
    And I click "More actions"
    And I click "Add task"
    And I fill form with:
      | Subject | Account task |
    And I click "Create Task"
    And I go to Activities/ Tasks
    Then I should see "Customer 1"
    And I should not see "Test string Customer 1"
