@fix-BB-9401
@automatically-ticket-tagged
Feature: Registration flow and error messages

  Scenario: Registration page is visible and after successful registration user should see correct message
    Given I am on "customer/user/registration"
    And Page title equals to "Registration"
    And I should see a "Registration Form" element
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I press "Create An Account"
    Then I should see "Please check your email to complete registration"

  Scenario: Error message should has correct container and text when user already present
    Given I am on "customer/user/registration"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | First Name       | Ruth                     |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I press "Create An Account"
    Then I should see that "Customer User Registration Error Container" contains "This value is already used."

  Scenario: Error message has correct container and text when registration form has empty field
    Given I am on "customer/user/registration"
    And I fill "Registration Form" with:
      | Company Name     | OroCommerce              |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I press "Create An Account"
    Then I should see that "Customer User Registration Error Container" contains "This value should not be blank."
