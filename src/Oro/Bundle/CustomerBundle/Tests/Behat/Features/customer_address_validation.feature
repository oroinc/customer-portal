@ticket-BB-14525
Feature: Customer address validation
  In order to to manage addresses for Customer
  As an administrator
  I want to see validation related to First/Last names and Organization for Address on Customer's edit page

  Scenario: Create customer with address and see validation errors
    Given I login as administrator
    And I go to Customers / Customers
    And I click "Create Customer"
    When I fill form with:
      | Name            | Test customer |
      | Country         | Aland Islands |
      | Street          | Test street   |
      | City            | Test city     |
      | Zip/Postal Code | 111111        |
    And I save form
    Then I should see "First Name and Last Name or Organization should not be blank."
    And I should see "Last Name and First Name or Organization should not be blank."
    And I should see "Organization or First Name and Last Name should not be blank."
    When I fill form with:
      | Organization | Test Organization |
    And I save form
    Then I should see "Customer has been saved" flash message
