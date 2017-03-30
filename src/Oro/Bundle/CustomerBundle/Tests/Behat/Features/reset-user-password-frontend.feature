@fixture-BuyerCustomerWithRequestedResetPasswordFixture.yml
Feature: User password changes
  In order to manage user password
  As an Buyer
  I want to be able to change password

  Scenario: Change password to password with low complexity
    Given Ryan1Range@example.org customer user followed the link to change the password
    And I fill form with:
      | Password         | 0 |
      | Confirm Password | 0 |
    When I press "Create"
    Then I should see validation errors:
      | Password | The password must be at least 2 characters long |
