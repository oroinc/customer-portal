@fix-BB-9401
@automatically-ticket-tagged
Feature: Registration flow and error messages

  Scenario: Registration page is visible and after successful registration user should see correct message
    Given I am on homepage
    And I click "Register"
    And Page title equals to "Registration"
    And I should see a "Registration Form" element
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create An Account"
    Then I should see "Please check your email to complete registration"

  Scenario: Error message should has correct container and text when user already present
    Given I click "Register"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create An Account"
    Then I should see that "Customer User Registration Error Container" contains "This email is already used."

  Scenario: Error message has correct container and text when registration form has empty field
    Given I click "Register"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create An Account"
    Then I should see that "Customer User Registration Error Container" contains "This value should not be blank."

  Scenario: Company Name field validation
    Given I am on the homepage
    And click "Sign In"
    And click "Create An Account"
    When I fill form with:
      | First Name        | Dan           |
      | Last Name         | Marini        |
      | Email Address     | test@test.com |
      | Password          | aA123123      |
      | Confirm Password  | aA123123      |
    And I type "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lobortis pulvinar consequat rutrum! Ullamcorper consequat augue aliquet cubilia... Malesuada maecenas nam non in risus dolor duis. Netus vehicula erat pulvinar! Dis ac iaculis hymenaeos accumsan feugiat lorem ut. Leo vestibulum parturient facilisi non; Augue dictum in venenatis varius: Dignissim magnis ut habitasse eleifend, magna rutrum dui justo. Fringilla tincidunt nascetur... Massa porta inceptos; Morbi et primis pulvinar porttitor! Tortor purus dis purus habitasse! Tortor hymenaeos viverra condimentum odio scelerisque per: Phasellus phasellus purus... Ornare gravida risus elementum hac nisl. Dapibus dui justo magnis sagittis porttitor! Blandit nec velit? Bibendum porta fames sodales mattis dui! Vehicula vulputate sed netus cras non tristique! Maecenas montes sodales gravida adipiscing, hac cum posuere nullam litora fusce, at donec inceptos eget, parturient odio cum potenti ac! Adipiscing ullamcorper eleifend." in "Company Name"
    And click "Create An Account"
    Then I should see that "Customer User Registration Error Container" contains "This value is too long. It should have 255 characters or less."
