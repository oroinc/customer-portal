@ticket-BB-14759
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Customer User address validation
  In order to to manage addresses for Customer User
  As an administrator
  I want to see validation related to First/Last names and Organization for Address on Customer User's edit page

  Scenario: Create customer user with address and see validation errors
    Given I login as administrator
    And I go to Customers / Customer Users
    And I click Edit first customer in grid
    When I fill form with:
      | Label           | Primary address |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I save form
    Then I should see "Customer User Address Form" validation errors:
      | First Name   | First Name and Last Name or Organization should not be blank. |
      | Last Name    | Last Name and First Name or Organization should not be blank. |
      | Organization | Organization or First Name and Last Name should not be blank. |
    When I fill form with:
      | Organization | Test Organization |
    And I save form
    Then I should see "Customer User has been saved" flash message
