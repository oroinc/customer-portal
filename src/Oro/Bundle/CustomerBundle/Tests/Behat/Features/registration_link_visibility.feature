@regression
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Registration Link Visibility
  In order to quickly register a new account to make purchases
  As a Buyer
  I want a clearly visible Register link at every Commerce page

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Check that registration link is visible by default
    Given I proceed as the User
    And I am on homepage
    And I should see "Register"
    Then I click "Register"
    And Page title equals to "Registration"
    And I should see a "Registration Form" element

  Scenario: Check that registration link is not visible after disable "Show Registration Link" option
    Given I proceed as the Admin
    And login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Show Registration Link" field
    And fill in "Show Registration Link" with "false"
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    Then I am on homepage
    And I should not see "Register"

  Scenario: Check that registration link is not visible after disable "Registration Allowed" option
    Given I proceed as the Admin
    And uncheck "Use default" for "Registration Allowed" field
    And fill "Customer Users Registration form" with:
      | Registration Allowed   | false |
      | Show Registration Link | true  |
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    Then I reload the page
    And I should not see "Register"

  Scenario: Check that registration link is visible after enable all needed options
    Given I proceed as the Admin
    And fill "Customer Users Registration form" with:
      | Registration Allowed   | true |
      | Show Registration Link | true |
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    Then I reload the page
    And I should see "Register"
    Then I signed in as AmandaRCole@example.org on the store frontend
    And I should not see "Register"

  Scenario: Check that registration link is not visible when disabled in website configuration
    Given I proceed as the Admin
    And login as administrator
    And go to System/ Websites
    And click "Configuration" on row "Default" in grid
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use Organization" for "Show Registration Link" field
    And fill in "Show Registration Link" with "false"
    And submit form
    And I should see "Configuration saved" flash message
    And I proceed as the User
    Then I am on homepage
    And I should not see "Register"
