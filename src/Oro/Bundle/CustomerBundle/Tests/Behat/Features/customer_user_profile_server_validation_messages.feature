@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Customer user profile server validation messages
  In order to understand why my profile changes were rejected
  As a customer user
  I need to see server validation messages as plain text

  Scenario: Change email with an incorrect current password
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "My Profile"
    When I click "Customer User Profile Edit Email"
    And I fill form with:
      | Password | WrongPassword1              |
      | Email    | NancyJSalleeNew@example.org |
    And I click "Save"
    Then I should see validation errors:
      | Password | This value should be the user's current password. |

  Scenario: Change password with an incorrect current password
    Given I click "Account Dropdown"
    And I click "My Profile"
    When I click "Customer User Profile Edit Password"
    And I fill form with:
      | Password         | WrongPassword1 |
      | New Password     | NeWpAsW0RdDDD  |
      | Confirm Password | NeWpAsW0RdDDD  |
    And I click "Save"
    Then I should see validation errors:
      | Password | This value should be the user's current password. |
