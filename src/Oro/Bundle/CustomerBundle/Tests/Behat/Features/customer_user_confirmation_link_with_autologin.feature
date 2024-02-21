@ticket-BB-22342
Feature: Customer user confirmation link with autologin
  In order to be sure that confirmation link in registratation email works well
  As a User
  I should be able to confirm my account with enabled autologin.

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Enable autologin
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Auto login" field
    And I check "Auto login"
    And I click "Save settings"

  Scenario: Check when register with enabled Confirmation and Auto Login
    Given I proceed as the User
    And I am on the homepage
    And I click "Log In"
    And I click "Create An Account"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Front1                   |
      | Last Name        | LastN                    |
      | Email Address    | FrontULastN1@example.org |
      | Password         | FrontULastN1@example.org |
      | Confirm Password | FrontULastN1@example.org |
    When I click "Create An Account"
    Then I should see "Log In"
    And I should not see "My Account"
    And email with Subject "Confirmation of account registration" containing the following was sent:
      | Body | Please follow this link to confirm your email address: Confirm |
    When I follow "Confirm" link from the email
    Then I should not see "404 Not Found"
    And I should see "Confirmation successful" flash message
    And I should not see "Log In"
    And I should see "My Account"
    And I click "Account Dropdown"
    Then I click "Sign Out"

  Scenario: Disable autologin
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And I uncheck "Auto login"
    And I click "Save settings"

  Scenario: Check when register with enabled Confirmation and disabled Auto Login
    Given I proceed as the User
    And I am on the homepage
    And I click "Log In"
    And I click "Create An Account"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Front2                   |
      | Last Name        | LastN                    |
      | Email Address    | FrontULastN2@example.org |
      | Password         | FrontULastN2@example.org |
      | Confirm Password | FrontULastN2@example.org |
    When I click "Create An Account"
    Then I should see "Log In"
    And I should not see "My Account"
    And email with Subject "Confirmation of account registration" containing the following was sent:
      | Body | Please follow this link to confirm your email address: Confirm |
    When I follow "Confirm" link from the email
    Then I should not see "404 Not Found"
    And I should see "Confirmation successful" flash message
    And I should see "Log In"
    And I should not see "My Account"

  Scenario: Disable confirmation
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Confirmation Required" field
    And I uncheck "Confirmation Required"
    And I click "Save settings"

  Scenario: Check when register with disabled Confirmation and Auto Login
    Given I proceed as the User
    And I am on the homepage
    And I click "Log In"
    And I click "Create An Account"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Front3                   |
      | Last Name        | LastN                    |
      | Email Address    | FrontULastN3@example.org |
      | Password         | FrontULastN3@example.org |
      | Confirm Password | FrontULastN3@example.org |
    When I click "Create An Account"
    Then I should see "Log In"
    And I should not see "My Account"

  Scenario: Enable autologin
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And I check "Auto login"
    And I click "Save settings"

  Scenario: Check when register with disabled Confirmation and Enabled Auto Login
    Given I proceed as the User
    And I am on the homepage
    And I click "Log In"
    And I click "Create An Account"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Front4                   |
      | Last Name        | LastN                    |
      | Email Address    | FrontULastN4@example.org |
      | Password         | FrontULastN4@example.org |
      | Confirm Password | FrontULastN4@example.org |
    When I click "Create An Account"
    Then I should not see "Log In"
    And I should see "My Account"
