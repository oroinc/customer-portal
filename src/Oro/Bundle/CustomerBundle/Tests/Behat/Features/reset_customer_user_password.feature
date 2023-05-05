@ticket-BB-20969
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Reset customer user password
  In order to manage customer user's accounts
  As an Administrator
  I need to be able to reset customer user's passwords

  Scenario: Feature Background
    Given sessions active:
      | Admin        | first_session  |
      | Unauthorized | second_session |

  Scenario: Ensure customer user can log in
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I fill form with:
      | Email Address | NancyJSallee@example.org |
      | Password      | NancyJSallee@example.org |
    When I click "Sign In"
    Then I should see "Signed in as: Nancy Sallee"
    And I click "Sign Out"

  Scenario: Reset customer user password from the admin panel
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers/Customer Users
    When I click "Reset password" on row "NancyJSallee@example.org" in grid
    Then should see "Customer user NancyJSallee@example.org will receive the email to reset password and will be disabled from login." in confirmation dialogue
    When I click "Reset" in confirmation dialogue
    Then I should see "Password reset request has been sent to NancyJSallee@example.org." flash message
    And Email should contains the following:
      | Subject | Please reset your password                                               |
      | To      | NancyJSallee@example.org                                                 |
      | Body    | Hi, Nancy Sallee!                                                        |
      | Body    | The administrator has requested a password reset for your user profile.  |
    And I remember "RESET PASSWORD" link from the email
    And I should see NancyJSallee@example.org in grid with following data:
      | Password | Reset |

  Scenario: Check that customer user cannot log in
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I fill form with:
      | Email Address | NancyJSallee@example.org |
      | Password      | NancyJSallee@example.org |
    When I click "Sign In"
    Then I should see "Your login was unsuccessful"

  Scenario: Reset password by emails link
    Given I proceed as the Unauthorized
    And I follow remembered "RESET PASSWORD" link from the email
    When I fill form with:
      | Password         | NancyJSallee@example.org1 |
      | Confirm Password | NancyJSallee@example.org1 |
    And click "Create"
    Then I should see "Password was created successfully."

  Scenario: Login with new password
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I fill form with:
      | Email Address | NancyJSallee@example.org |
      | Password      | NancyJSallee@example.org1 |
    When I click "Sign In"
    Then I should see "Signed in as: Nancy Sallee"
    And I click "Sign Out"

  Scenario: Reset customer user password from storefront
    Given I proceed as the Unauthorized
    And I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Users"
    When I click "Reset password" on row "NancyJSallee@example.org" in grid
    Then should see "Customer user NancyJSallee@example.org will receive the email to reset password and will be disabled from login." in confirmation dialogue
    When I click "Reset" in confirmation dialogue
    Then I should see "Password reset request has been sent to NancyJSallee@example.org." flash message
    And Email should contains the following:
      | Subject | Please reset your password                                               |
      | To      | NancyJSallee@example.org                                                 |
      | Body    | Hi, Nancy Sallee!                                                        |
      | Body    | The administrator has requested a password reset for your user profile.  |
    And I remember "RESET PASSWORD" link from the email
    And I should see NancyJSallee@example.org in grid with following data:
      | Password | Reset |
    And I click "Sign Out"

  Scenario: Check that customer user cannot log in after the password was reset on store front
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I fill form with:
      | Email Address | NancyJSallee@example.org |
      | Password      | NancyJSallee@example.org1 |
    When I click "Sign In"
    Then I should see "Your login was unsuccessful"

  Scenario: Reset password by emails link
    Given I proceed as the Unauthorized
    And I follow remembered "RESET PASSWORD" link from the email
    When I fill form with:
      | Password         | NancyJSallee@example.org2 |
      | Confirm Password | NancyJSallee@example.org2 |
    And click "Create"
    Then I should see "Password was created successfully."

  Scenario: Login with new password
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Sign In"
    And I fill form with:
      | Email Address | NancyJSallee@example.org |
      | Password      | NancyJSallee@example.org2 |
    When I click "Sign In"
    Then I should see "Signed in as: Nancy Sallee"
    And I click "Sign Out"
