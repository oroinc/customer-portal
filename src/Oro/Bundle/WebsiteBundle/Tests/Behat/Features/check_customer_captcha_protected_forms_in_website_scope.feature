@regression
@behat-test-env
@ticket-BB-24484
@fixture-OroCustomerBundle:CustomerUserAmandaRCole.yml

Feature: Check customer captcha protected forms in website scope

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Check that User Reset Password Form and User Login Form is not visible
    Given I proceed as the Admin
    And I login as administrator
    When I go to System/ Websites
    And I click Configuration Default in grid
    And I follow "System Configuration/Integrations/CAPTCHA Settings" on configuration sidebar
    Then I should not see "Captcha Protect User Reset Password Form Checkbox"
    And I should not see "Captcha Protect User Login Form Checkbox"

  Scenario: Enable CAPTCHA protection
    When I uncheck "Use Organization" for "Enable CAPTCHA protection" field
    And I check "Enable CAPTCHA protection"

    And uncheck "Use Organization" for "CAPTCHA service" field
    And I fill in "CAPTCHA service" with "Dummy"

    And uncheck "Use Organization" for "Protect Forms" field
    And I check "Customer User Registration Form"
    And I check "Customer User Reset Password Form"
    And I check "Customer User Login Form"

    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Check CAPTCHA protection for Customer User Reset Password Form
    Given I proceed as the Buyer

    When I am on the homepage
    And I click "Log In"
    And I click "Forgot Password?"
    Then I should see "Reset Password"
    And I should see "Captcha"

    When I fill in "Email" with "AmandaRCole@example.org"
    And I fill in "Captcha" with "invalid"
    And I click "Reset Password"
    Then I should see "The form cannot be sent because you did not passed the anti-bot validation. If you are a human, please contact us."

    When I fill in "Email" with "AmandaRCole@example.org"
    And I fill in "Captcha" with "valid"
    And I click "Reset Password"
    Then I should see "Please check AmandaRCole@example.org for a reset link and follow it to set a new password."

  Scenario: Check CAPTCHA protection for Customer User Registration Form
    When I click "Sign Up"
    Then I should see "Create an account"
    And I should see "Captcha"

    When I fill "Registration Form" with:
      | Company Name     | OroCommerce       |
      | First Name       | Ruth              |
      | Last Name        | Maxwell           |
      | Email            | ruth@example.org  |
      | Password         | Ruth@example.org1 |
      | Confirm Password | Ruth@example.org1 |
      | Captcha          | invalid           |
    And I click "Create Account"
    Then I should see "The form cannot be sent because you did not passed the anti-bot validation. If you are a human, please contact us."

    When I fill "Registration Form" with:
      | Company Name     | OroCommerce       |
      | First Name       | Ruth              |
      | Last Name        | Maxwell           |
      | Email            | ruth@example.org  |
      | Password         | Ruth@example.org1 |
      | Confirm Password | Ruth@example.org1 |
      | Captcha          | valid             |
    And I click "Create Account"
    Then I should see "Please check your email to complete registration" flash message

  Scenario: Check CAPTCHA protection for Customer User Reset Password Form
    When I click "Log In"
    Then I should see "Captcha"
    When I fill in "Email" with "AmandaRCole@example.org"
    And I fill in "Password" with "AmandaRCole@example.org"
    And I fill in "Captcha" with "invalid"
    And I click "Log In Button"
    Then I should see "The form cannot be sent because you did not passed the anti-bot validation. If you are a human, please contact us."
    When I fill in "Email" with "AmandaRCole@example.org"
    And I fill in "Password" with "AmandaRCole@example.org"
    And I fill in "Captcha" with "valid"
    And I click "Log In Button"
    Then I should not see "Log In"
