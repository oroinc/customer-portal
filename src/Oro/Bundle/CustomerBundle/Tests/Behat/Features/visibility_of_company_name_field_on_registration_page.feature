@regression
Feature: Visibility of Company Name Field on registration page
  In order to use Commerce store as B2C store
  As an administrator
  I want to make company name optional at registration form

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Check that Company Name Field is visible by default
    Given I proceed as the User
    And I am on homepage
    Then I click "Register"
    And I should see a "Registration Form" element
    And I should see that "Registration Form" contains "Company Name"

  Scenario: Check that registration link is not visible after disabling "Require Company Name" option
    Given I proceed as the Admin
    And login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Require Company Name" field
    And fill in "Require Company Name" with "false"
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    Then I reload the page
    And I should see that "Registration Form" does not contain "Company Name"

  Scenario: Check correct registration flow when "Require Company Name" option is disabled
    Given I fill "Registration Form" with:
      | First Name       | New                      |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create An Account"
    Then I should see "Please check your email to complete registration"

  Scenario: Check that company name consists of first name and last name when "Require Company Name" option is disabled
    Given I proceed as the Admin
    And I go to Customers/Customer Users
    And I should see following grid:
      | CUSTOMER     | FIRST NAME | LAST NAME | EMAIL ADDRESS |
      | New Maxwell  | New        | Maxwell   | RuthWMaxwell@example.org |

  Scenario: Check that company name field is not visible when disabled in website configuration
    Given I proceed as the Admin
    And login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And check "Use default" for "Require Company Name" field
    And submit form
    And I should see "Configuration saved" flash message
    And go to System/ Websites
    And click "Configuration" on row "Default" in grid
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use Organization" for "Require Company Name" field
    And fill in "Require Company Name" with "false"
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    Then I click "Register"
    And I should see that "Registration Form" does not contain "Company Name"
