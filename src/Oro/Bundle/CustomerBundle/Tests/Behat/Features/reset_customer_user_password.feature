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
    And I click "Log In"
    And I fill "Customer Login Form" with:
      | Email    | NancyJSallee@example.org |
      | Password | NancyJSallee@example.org |
    When I click "Log In Button"
    Then I should see "Nancy Sallee"
    And I click "Account Dropdown"
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
      | Subject | Please reset your password                                              |
      | To      | NancyJSallee@example.org                                                |
      | Body    | Hello, Nancy Sallee!                                                    |
      | Body    | The administrator has requested a password reset for your user profile. |
    And I remember "Reset Password" link from the email
    And I should see NancyJSallee@example.org in grid with following data:
      | Password | Reset |

  Scenario: Check that customer user cannot log in
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Log In"
    And I fill "Customer Login Form" with:
      | Email    | NancyJSallee@example.org |
      | Password | NancyJSallee@example.org |
    When I click "Log In Button"
    Then I should see "Your login was unsuccessful"

  Scenario: Reset password by emails link
    Given I proceed as the Unauthorized
    And I follow remembered "Reset Password" link from the email
    When I fill "Customer Reset Form" with:
      | Password         | NancyJSallee@example.org1 |
      | Confirm Password | NancyJSallee@example.org1 |
    And click "Save Changes"
    Then I should see "Password successfully changed"

  Scenario: Login with new password
    Given I fill "Customer Login Form" with:
      | Email    | NancyJSallee@example.org |
      | Password | NancyJSallee@example.org1 |
    When I click "Log In Button"
    Then I should not see "404 Not Found"
    And should see "Nancy Sallee"
    And I click "Account Dropdown"
    And I click "Sign Out"

  Scenario: Reset customer user password from storefront
    Given I proceed as the Unauthorized
    And I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Users"
    When I click "Reset password" on row "NancyJSallee@example.org" in grid
    Then should see "Customer user NancyJSallee@example.org will receive the email to reset password and will be disabled from login." in confirmation dialogue
    When I click "Reset" in confirmation dialogue
    Then I should see "Password reset request has been sent to NancyJSallee@example.org." flash message
    And Email should contains the following:
      | Subject | Please reset your password                                              |
      | To      | NancyJSallee@example.org                                                |
      | Body    | Hello, Nancy Sallee!                                                    |
      | Body    | The administrator has requested a password reset for your user profile. |
    And I remember "Reset Password" link from the email
    And I should see NancyJSallee@example.org in grid with following data:
      | Password | Reset |
    And I click "Account Dropdown"
    And I click "Sign Out"

  Scenario: Check that customer user cannot log in after the password was reset on store front
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Log In"
    And I fill "Customer Login Form" with:
      | Email    | NancyJSallee@example.org |
      | Password | NancyJSallee@example.org1 |
    When I click "Log In Button"
    Then I should see "Your login was unsuccessful"

  Scenario: Reset password by emails link
    Given I proceed as the Unauthorized
    And I follow remembered "Reset Password" link from the email
    When I fill "Customer Reset Form" with:
      | Password         | NancyJSallee@example.org2 |
      | Confirm Password | NancyJSallee@example.org2 |
    And click "Save Changes"
    Then I should see "Password successfully changed"

  Scenario: Login with new password
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Log In"
    And I fill "Customer Login Form" with:
      | Email    | NancyJSallee@example.org |
      | Password | NancyJSallee@example.org2 |
    When I click "Log In Button"
    Then I should see "Nancy Sallee"
    And I click "Account Dropdown"
    And I click "Sign Out"

  Scenario: Update customer password after reset password
    Given I proceed as the Admin
    And I go to Customers/Customer Users
    When I click "Reset password" on row "NancyJSallee@example.org" in grid
    Then should see "Customer user NancyJSallee@example.org will receive the email to reset password and will be disabled from login." in confirmation dialogue
    When I click "Reset" in confirmation dialogue
    Then I should see "Password reset request has been sent to NancyJSallee@example.org." flash message
    And I remember "Reset Password" link from the email
    And I should see NancyJSallee@example.org in grid with following data:
      | Password | Reset |
    And I click edit NancyJSallee@example.org in grid
    When fill "Customer User Form" with:
      | Password                             | NancyJSallee1  |
      | Confirm Password                     | NancyJSallee1  |
    And save and close form
    Then I should see "Customer User has been saved"
    Then I should see Customer User with:
      | Password | Active |

  Scenario: Ensure customer user can log in after password reset and set new password
    Given I proceed as the Unauthorized
    And I am on the homepage
    And I click "Log In"
    And I fill "Customer Login Form" with:
      | Email    | NancyJSallee@example.org |
      | Password | NancyJSallee1            |
    When I click "Log In Button"
    Then I should see "Nancy Sallee"
    And I click "Account Dropdown"
    And I click "Sign Out"
