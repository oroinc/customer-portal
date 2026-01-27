@ticket-BB-26700
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Disable customer user login password form
  In order to have ability to manage customer user logins
  As administrator
  I need to have ability to disable customer user login form with password functionality

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Default login
    Given I proceed as the User
    When I go to "/customer/user/login"
    Then I should be on Customer User Login page
    And I should see "Email"
    And I should see "Password"
    And I should see "Log in"
    When I fill form with:
      | Email    | AmandaRCole@example.org |
      | Password | AmandaRCole@example.org |
    And I click "Customer User Sign In"
    Then I should see "Amanda Cole"

  Scenario: Check Customer User page password functionality with enabled form
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customer /Customer Users
    Then I should see following actions for AmandaRCole@example.org in grid:
      | Reset password |
    When I click view AmandaRCole@example.org in grid
    Then I should see "Reset password"
    When I go to Customer /Customer Users
    And I click "Create Customer User"
    Then I should see "Password"
    And I should see "Confirm Password"

  Scenario: Disable login form
    Given I go to System / Configuration
    When I follow "Commerce/Customer/Customer User Login" on configuration sidebar
    Then I should see "Password change policy"
    And I should see "Login attempts"
    When I uncheck "Use default" for "Enable Username/Password Login" field
    And I uncheck "Enable Username/Password Login"
    Then I should not see "Password change policy"
    And I should not see "Login attempts"
    When I click "Save settings"
    Then I should see "Configuration saved" flash message

  Scenario: Check Customer User page password functionality with disabled form
    Given I go to Customer /Customer Users
    Then I should not see following actions for AmandaRCole@example.org in grid:
      | Reset password |
    When click view AmandaRCole@example.org in grid
    Then I should not see "Reset password"
    When I go to Customer /Customer Users
    And click "Create Customer User"
    Then I should not see "Password"
    And I should not see "Confirm Password"

  Scenario: Check Login page with disabled form
    Given I proceed as the User
    When I click "Account Dropdown"
    And click "Sign Out"
    Then I should not see text matching "Amanda Cole"
    When I go to "/customer/user/login"
    Then I should not see "Email"
    And I should not see "Password"
    And I should see "No login options are currently available. Please contact your administrator"
