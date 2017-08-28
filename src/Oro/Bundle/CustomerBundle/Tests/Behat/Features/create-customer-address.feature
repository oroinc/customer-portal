@ticket-BB-5982
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Create customer user address
  In order to to manage addresses for Customer User
  As an administrator
  I want to create new Address

  Scenario: Create customer user address and see validation errors
    Given I login as administrator
    And I go to Customers / Customer Users
    And I click on first customer in grid
    Then I should not see "Test billing address"

    When I press "+ New Address"
    And I fill form with:
      | Label           | Test billing address |
      | First name      | Test first name      |
      | Last name       |                      |
      | Street          | Test street          |
      | City            | Test city            |
      | Country         | Aland Islands        |
      | Zip/Postal Code | 111111               |
    And I press "Save"
    Then I should see "First Name and Last Name or Organization should not be blank."
    Then I should see "Last Name and First Name or Organization should not be blank."
    Then I should see "Organization or First Name and Last Name should not be blank."
    When I fill form with:
      | Organization | Test Organization |
    And I press "Save"
    Then I should see "Address saved" flash message
    And I should see "Test billing address"
