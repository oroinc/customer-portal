Feature: Creating Customer User
  In order to manage customer users
  As an Administrator
  I want to be able to create account for Customer User

  Scenario: Creating Customer User with low password complexity
    Given I login as administrator
    And go to Customers/Customer Users
    And follow "Create Customer User"
    And I fill form with:
      | Password         | 0 |
      | Confirm Password | 0 |
    When I save and close form
    Then I should see "The password must be at least 8 characters long and include a lower case letter and an upper case letter"
