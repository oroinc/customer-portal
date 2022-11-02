@ticket-BAP-17151
@fixture-OroCustomerBundle:CustomerUserWithExistingPasswordResetToken.yml

Feature: Expired reset password token regeneration
  In order to reset password
  As a Customer
  I should be able to regenerate expired reset password token

  Scenario: Expired reset password token regeneration
    Given I am on the homepage
    When I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | test@example.org |
    And I confirm reset password
    Then I should see "If there is a user account associated with test@example.org you will receive an email with a link to reset your password."
    And Email should contains the following:
      | To      | test@example.org            |
      | Subject | Reset Account User Password |
    And Email should not contains the following:
      | Body    | testConfirmationToken |
