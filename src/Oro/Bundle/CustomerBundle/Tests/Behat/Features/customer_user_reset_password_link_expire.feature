@ticket-BB-16157
@ticket-BB-24202
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Customer user reset password link expire

  Scenario: Feature Background
    Given sessions active:
      | Unauthorized | first_session  |
      | User         | second_session |

  Scenario: Change password for another customer user on back office not affected reset password link
    Given I proceed as the Unauthorized
    When I am on the homepage
    And I click "Log In"
    And I click "Forgot Password?"
    Then I should see "Enter the email address you used to log in, and we’ll send you a reset link."
    When I fill form with:
      | Email | AmandaRCole@example.org |
    And I click "Reset Password"
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page
    And I should not see "We’ve sent a reset code to your inbox. Please enter it below to proceed."

    When I proceed as the User
    And I login as administrator
    And I go to Customers/Customer Users
    And I click edit Amanda in grid
    And I fill form with:
      | Password         | NeWpAsW0Rd |
      | Confirm Password | NeWpAsW0Rd |
    And I save and close form
    Then I should see "Customer User has been saved" flash message

    When I proceed as the Unauthorized
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should see "Not Found"

  Scenario: Reset password link must be expire after customer user login
    Given I proceed as the Unauthorized
    When I am on the homepage
    And I click "Log In"
    And I click "Forgot Password?"
    And I fill form with:
      | Email | AmandaRCole@example.org |
    And I click "Reset Password"
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page

    When I proceed as the User
    And I signed in as AmandaRCole@example.org with password NeWpAsW0Rd on the store frontend
    And I proceed as the Unauthorized
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should see "Not Found"

  Scenario: Reset password link must be expire after customer user change his password
    Given I proceed as the Unauthorized
    When I am on the homepage
    And I click "Log In"
    And I click "Forgot Password?"
    And I fill form with:
      | Email | AmandaRCole@example.org |
    And I click "Reset Password"
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page

    When I proceed as the User
    And I click "Account Dropdown"
    And I click "My Profile"
    And I click "Edit"
    And I fill "Customer User Profile Form" with:
      | Password         | NeWpAsW0Rd    |
      | New Password     | NeWpAsW0RdDDD |
      | Confirm Password | NeWpAsW0RdDDD |
    And I click "Save"
    Then I should see "Customer User profile updated" flash message

    When I proceed as the Unauthorized
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should see "Not Found"

  Scenario: Reset password link must be expire after customer user change his email
    Given I proceed as the Unauthorized
    When I am on the homepage
    And I click "Log In"
    And I click "Forgot Password?"
    And I fill form with:
      | Email | AmandaRCole@example.org |
    And I click "Reset Password"
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page

    When I proceed as the User
    And I click "Account Dropdown"
    And I click "Edit Profile Button"
    And I fill "Customer User Profile Form" with:
      | Email | new-amanda-email@example.com |
    And I click "Save"
    Then I should see "Customer User profile updated" flash message

    When I proceed as the Unauthorized
    And I follow "[^\n]+\/customer\/user\/reset[^\<]+" link from the email
    Then I should see "Not Found"
