Feature: User password changes
  In order to manage user password
  As an Buyer
  I want to be able to change password

  Scenario: Request change password
    Given I am on homepage
    And I follow "Sign In"
    And I follow "Forgot Your Password?"
    And I fill form with:
      | Email Address | AmandaRCole@example.org |
    When I press "Request"
    Then I should see "Check Email"

  Scenario: Change password to password with low complexity
    Given AmandaRCole@example.org customer user followed the link to change the password
    And I fill form with:
      | Password         | 0 |
      | Confirm Password | 0 |
    When I press "Create"
    Then I should see "The password must be at least 8 characters long and include a lower case letter and an upper case letter"

