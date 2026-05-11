@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Change customer user email
  In order to secure customer user email changes
  As a customer user
  I need to confirm the email change to apply the changes

  Scenario: Change email address and cancel the changes
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I change configuration options:
      | oro_customer.email_change_verification_enabled | true |
      | oro_customer.confirmation_required             | true |
    And I click "Account Dropdown"
    When I click "My Profile"
    Then I should see "NancyJSallee@example.org"
    And I should not see "Confirmed"
    When I click "Customer User Profile Edit Email"
    And I fill form with:
      | Password | NancyJSallee@example.org |
      | Email    | NancyJSalleeNew@example.org  |
    And I click "Save"
    Then I should see "A confirmation email to change your email address was sent to your current email address. Please follow the link in it to complete the email change." flash message
    And I should see "Nancy Sallee"
    And I should see "NancyJSallee@example.org"
    And I should see "Confirmed"
    And I should see "NancyJSalleeNew@example.org"
    And I should see "Pending confirmation"
    When I follow "[^\n]+\/confirm-email-change[^\<]+" link from the email
    Then I should see "Change Email"
    And I should see "Confirm your email change from NancyJSallee@example.org to NancyJSalleeNew@example.org."
    And I should see "An additional confirmation email will be sent to your new email address."
    When I click "Cancel"
    Then I should see "Email change request was cancelled." flash message
    And I should not see "Confirmed"
    And I should not see "Pending confirmation"
    And I should not see "NancyJSalleeNew@example.org"

  Scenario: Change email address and follow all steps
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I change configuration options:
      | oro_customer.email_change_verification_enabled | true  |
      | oro_customer.confirmation_required             | true  |
    And I click "Account Dropdown"
    When I click "My Profile"
    Then I should see "NancyJSallee@example.org"
    And I should not see "Confirmed"
    When I click "Customer User Profile Edit Email"
    And I fill form with:
      | Password | NancyJSallee@example.org |
      | Email    | NancyJSalleeNew@example.org  |
    And I click "Save"
    Then I should see "A confirmation email to change your email address was sent to your current email address. Please follow the link in it to complete the email change." flash message
    And I should see "Nancy Sallee"
    And I should see "NancyJSallee@example.org"
    And I should see "Confirmed"
    And I should see "NancyJSalleeNew@example.org"
    And I should see "Pending confirmation"
    When I follow "[^\n]+\/confirm-email-change[^\<]+" link from the email
    Then I should see "Change Email"
    And I should see "Confirm your email change from NancyJSallee@example.org to NancyJSalleeNew@example.org."
    And I should see "An additional confirmation email will be sent to your new email address."

  Scenario: Confirm new email and finish email change
    When I click "Confirm"
    Then I should see "Confirmation email has been sent to NancyJSalleeNew@example.org." flash message
    When I follow "[^\n]+\/confirm-new-email[^\<]+" link from the email
    Then I should not see "NancyJSallee@example.org"
    And I should see "NancyJSalleeNew@example.org"

  Scenario: Change email address and follow all steps with disabled new email confirmation
    Given I change configuration options:
      | oro_customer.email_change_verification_enabled | true  |
      | oro_customer.confirmation_required             | false |
    And I click "Account Dropdown"
    When I click "My Profile"
    And I click "Customer User Profile Edit Email"
    And I fill form with:
      | Password | NancyJSallee@example.org |
      | Email    | NancyJSalleeSimple@example.org  |
    And I click "Save"
    Then I should see "A confirmation email to change your email address was sent to your current email address. Please follow the link in it to complete the email change." flash message
    And I should see "Nancy Sallee"
    And I should see "NancyJSalleeNew@example.org"
    And I should see "Confirmed"
    And I should see "NancyJSalleeSimple@example.org"
    And I should see "Pending confirmation"
    When I follow "[^\n]+\/confirm-email-change[^\<]+" link from the email
    Then I should see "Change Email"
    And I should see "Confirm your email change from NancyJSalleeNew@example.org to NancyJSalleeSimple@example.org."
    And I should not see "An additional confirmation email will be sent to your new email address."
    When I click "Confirm"
    Then I should not see "NancyJSalleeNew@example.org"
    And I should see "NancyJSalleeSimple@example.org"
