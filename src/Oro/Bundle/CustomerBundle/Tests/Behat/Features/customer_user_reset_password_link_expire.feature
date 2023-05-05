@regression
@ticket-BB-16157
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Customer user reset password link expire

  Scenario: Feature Background
    Given sessions active:
      | Unauthorized | first_session  |
      | User         | second_session |

  Scenario: Change password for another customer user on back office not affected reset password link
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | AmandaRCole@example.org |
    When I click "Request"
    And I follow link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page

    Given I proceed as the User
    And I login as administrator
    And I go to Customers/Customer Users
    And I click edit Amanda in grid
    When I fill form with:
      | Password         | NeWpAsW0Rd |
      | Confirm Password | NeWpAsW0Rd |
    And I save and close form
    Then I should see "Customer User has been saved" flash message

    Given I proceed as the Unauthorized
    Then I follow link from the email
    Then I should see "Not Found"

  Scenario: Reset password link must be expire after customer user login
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | AmandaRCole@example.org |
    When I click "Request"
    And I follow link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page

    Given I proceed as the User
    When I signed in as AmandaRCole@example.org with password NeWpAsW0Rd on the store frontend
    And I proceed as the Unauthorized
    And I follow link from the email
    Then I should see "Not Found"

  Scenario: Reset password link must be expire after customer user change his password
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | AmandaRCole@example.org |
    When I click "Request"
    And I follow link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page

    Given I proceed as the User
    When I follow "Account"
    And I click "Edit Profile Button"
    And I fill "Customer User Profile Form" with:
      | Password         | NeWpAsW0Rd    |
      | New Password     | NeWpAsW0RdDDD |
      | Confirm Password | NeWpAsW0RdDDD |
    And I click "Save"
    Then I should see "Customer User profile updated" flash message

    Given I proceed as the Unauthorized
    When I follow link from the email
    Then I should see "Not Found"

  Scenario: Reset password link must be expire after customer user change his email
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | AmandaRCole@example.org |
    When I click "Request"
    And I follow link from the email
    Then I should not see "Not Found"
    And I should be on Customer User Password Reset page

    Given I proceed as the User
    When I follow "Account"
    And I click "Edit Profile Button"
    And I fill "Customer User Profile Form" with:
      | Email Address | new-amanda-email@example.com |
    And I click "Save"
    Then I should see "Customer User profile updated" flash message

    Given I proceed as the Unauthorized
    When I follow link from the email
    Then I should see "Not Found"
