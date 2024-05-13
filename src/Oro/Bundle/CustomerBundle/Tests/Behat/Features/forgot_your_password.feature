@regression
@ticket-BB-14229
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Forgot your password
  In order to restore password
  As a Customer
  I want to have the forgot password functionality

  Scenario: Verify validation errors
    Given I am on the homepage
    When I click "Log In"
    And I click "Forgot Password?"

    When I fill "Customer Password Request Form" with:
      | Email | oro |
    Then I should see validation errors:
      | Email | This value is not a valid email address. |
    And I should not see validation errors:
      | Email | This value should not be blank.          |

    When I fill "Customer Password Request Form" with:
      | Email |  |
    Then I should see validation errors:
      | Email | This value should not be blank. |
    And I should not see validation errors:
      | Email | This value is not a valid email address. |

  Scenario: Verify not existing email address
    Given I am on the homepage
    When I click "Log In"
    And I click "Forgot Password?"
    And I fill "Customer Password Request Form" with:
      | Email | nonexisting@example.com |
    And I click "Reset Password"
    Then I should see "Please check nonexisting@example.com for a reset link and follow it to set a new password."

  Scenario: Verify recovery message
    Given I am on the homepage
    When I click "Log In"
    And I click "Forgot Password?"
    And I fill "Customer Password Request Form" with:
      | Email | AmandaRCole@example.org |
    And I click "Reset Password"
    Then I should see "Please check AmandaRCole@example.org for a reset link and follow it to set a new password."
    And Email should contains the following:
      | Subject | Reset Account User Password                                          |
      | To      | AmandaRCole@example.org                                              |
      | Body    | Hello, AmandaRCole@example.org!                                      |
      | Body    |  To reset your password - please visit                               |
      | Body    |  customer/user/reset?token=                                          |
