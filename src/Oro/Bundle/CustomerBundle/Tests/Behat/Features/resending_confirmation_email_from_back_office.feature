@ticket-BB-16095

Feature: Resending confirmation email from back office
  In order to allow customer users to register and use the service
  As an administrator
  I want the confirmation email to contain a link with valid token when resending confirmation email from back office

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Register new user and don't follow the confirmation link
    Given I proceed as the User
    And I am on homepage
    Then I click "Register"
    Given I fill "Registration Form" with:
      | Company Name     | Company Inc              |
      | First Name       | New                      |
      | Last Name        | Maxwell                  |
      | Email Address    | RuthWMaxwell@example.org |
      | Password         | RuthWMaxwell123          |
      | Confirm Password | RuthWMaxwell123          |
    When I click "Create An Account"
    Then I should see "Please check your email to complete registration" flash message
    And email with Subject "Confirmation of account registration" containing the following was sent:
      | Body | Please follow this link to confirm your email address: Confirm |

  Scenario: Resend Confirmation email from back office and check if the confirmation link works
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers / Customer Users
    And I click view "RuthWMaxwell@example.org" in grid
    And I click "Resend Confirmation Email"
    And email with Subject "Confirmation of account registration" containing the following was sent:
      | Body | Please follow this link to confirm your email address: Confirm |
    And I follow "Confirm" link from the email
    Then I should not see "404 Not Found"
    And I should see "Confirmation successful" flash message
    When I follow "Confirm" link from the email
    Then I should see "404 Not Found"
    And I should see "This confirmation link may have already been used or is expired. Please contact us if you have issues with registration." flash message
