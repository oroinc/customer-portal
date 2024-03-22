@ticket-BAP-17151
@fixture-OroCustomerBundle:CustomerUserWithExistingPasswordResetToken.yml

Feature: Expired reset password token regeneration
  In order to reset password
  As a Customer
  I should be able to regenerate expired reset password token

  Scenario: Expired reset password token regeneration
    Given I am on the homepage
    When I click "Log In"
    And I click "Forgot Password?"
    And I fill "Customer Password Request Form" with:
      | Email | test@example.org |
    And I click "Reset Password"
    Then I should see "Please check test@example.org for a reset link and follow it to set a new password."
    And Email should contains the following:
      | To      | test@example.org            |
      | Subject | Reset Account User Password |
    And Email should not contains the following:
      | Body    | testConfirmationToken |
