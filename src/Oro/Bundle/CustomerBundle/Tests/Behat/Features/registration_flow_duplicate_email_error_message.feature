@regression
@fix-BB-9401
@ticket-BB-24174
@automatically-ticket-tagged
Feature: Registration flow duplicate email error message

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Enable "Customer User Email Enumeration Protection" configuration option
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Customer User Email Enumeration Protection" field
    And I uncheck "Customer User Email Enumeration Protection"
    When I save form
    Then I should see "Configuration saved" flash message

  Scenario: Registration page is visible and after successful registration user should see correct message
    Given I proceed as the Buyer
    Given I am on homepage
    And I click "Sign Up"
    And Page title equals to "Sign Up"
    And I should see a "Registration Form" element
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create Account"
    Then I should see "Please check your email to complete registration" flash message and I close it

  Scenario: Error message should has correct container and text when user already present
    Given I click "Sign Up"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create Account"
    Then I should see that "Customer User Registration Error Container" contains "This email is already used."
