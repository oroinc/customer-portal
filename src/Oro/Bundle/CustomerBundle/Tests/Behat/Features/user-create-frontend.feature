@fixture-BuyerCustomerFixture.yml
Feature: Creating User
  In order to manage users
  As an Buyer
  I want to be able to create account for User

  Scenario: Creating User with low password complexity
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I follow "Account"
    And I follow "Users"
    And I press "Create User"
    And I fill form with:
      | Password         | 0 |
      | Confirm Password | 0 |
    When I press "Save"
    Then I should see validation errors:
      | Password | The password must be at least 8 characters long and include a lower case letter and an upper case letter |
