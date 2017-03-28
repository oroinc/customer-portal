Feature: Creating User
  In order to manage users
  As an Buyer
  I want to be able to create account for User

  Scenario: Creating User with low password complexity
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Users"
    And I press "Create User"
    And I fill form with:
      | Password         | 0 |
      | Confirm Password | 0 |
    When I save form
    Then I should see "The password must be at least 8 characters long and include a lower case letter and an upper case letter"
