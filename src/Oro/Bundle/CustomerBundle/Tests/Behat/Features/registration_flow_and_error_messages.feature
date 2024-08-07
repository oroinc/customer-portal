@regression
@fix-BB-9401
@ticket-BB-24174
@automatically-ticket-tagged
Feature: Registration flow and error messages

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
    And I check "Customer User Email Enumeration Protection"
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
    Then I should see "Please check your email to complete registration" flash message
    And Email should contains the following "unauthorized attempt to register an account" text

  Scenario: Error message has correct container and text when registration form has empty field
    Given I click "Sign Up"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create Account"
    Then I should see that "Customer User Registration Error Container" contains "This value should not be blank."

  Scenario: Company Name field validation
    Given I am on the homepage
    And click "Log In"
    And I click "Sign Up"
    When I fill form with:
      | First Name       | Dan           |
      | Last Name        | Marini        |
      | Email            | test@test.com |
      | Password         | aA123123      |
      | Confirm Password | aA123123      |
    And I type "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lobortis pulvinar consequat rutrum! Ullamcorper consequat augue aliquet cubilia... Malesuada maecenas nam non in risus dolor duis. Netus vehicula erat pulvinar! Dis ac iaculis hymenaeos accumsan feugiat lorem ut. Leo vestibulum parturient facilisi non; Augue dictum in venenatis varius: Dignissim magnis ut habitasse eleifend, magna rutrum dui justo. Fringilla tincidunt nascetur... Massa porta inceptos; Morbi et primis pulvinar porttitor! Tortor purus dis purus habitasse! Tortor hymenaeos viverra condimentum odio scelerisque per: Phasellus phasellus purus... Ornare gravida risus elementum hac nisl. Dapibus dui justo magnis sagittis porttitor! Blandit nec velit? Bibendum porta fames sodales mattis dui! Vehicula vulputate sed netus cras non tristique! Maecenas montes sodales gravida adipiscing, hac cum posuere nullam litora fusce, at donec inceptos eget, parturient odio cum potenti ac! Adipiscing ullamcorper eleifend." in "Company Name"
    And click "Create Account"
    Then I should see that "Customer User Registration Error Container" contains "This value is too long. It should have 255 characters or less."
