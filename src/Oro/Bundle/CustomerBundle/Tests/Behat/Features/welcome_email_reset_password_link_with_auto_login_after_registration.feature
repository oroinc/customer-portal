@regression
@ticket-BB-27511

Feature: Welcome email reset password link with auto login after registration
  In order to recover password after registering with auto-login enabled
  As a Customer User
  I need the reset password link in the welcome email to lead to a valid page

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Configure registration without confirmation and with auto-login
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Confirmation Required" field
    And I uncheck "Confirmation Required"
    And uncheck "Use default" for "Auto login" field
    And I check "Auto login"
    And I click "Save settings"

  Scenario: Register a new customer user and verify the reset password link in the welcome email works
    Given I proceed as the User
    And I am on the homepage
    And I click "Log In"
    And I click "Sign Up"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce         |
      | First Name       | John                |
      | Last Name        | Doe                 |
      | Email Address    | JohnDoe1@example.org |
      | Password         | JohnDoe1@example.org |
      | Confirm Password | JohnDoe1@example.org |
    When I click "Create Account"
    Then I should see "My Account"
    And I should not see "Log In"
    And email with Subject "Welcome: John Doe" containing the following was sent:
      | Body | recover it here |
    And I remember "here" link from the email
    When I follow remembered "here" link from the email
    Then I should not see "404 Not Found"
    And I should see "My Profile"
    And I click "Account Dropdown"
    And I click "Sign Out"
    When I follow remembered "here" link from the email
    Then I should not see "404 Not Found"
    And I should see "Enter the email address you used to log in, and we’ll send you a reset link."
