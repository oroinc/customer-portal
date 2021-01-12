@ticket-BB-14229
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Forgot your password
  In order to restore password
  As a Customer
  I want to have the forgot password functionality

  Scenario: Verify validation errors
    Given I am on the homepage
    When I click "Sign In"
    And I click "Forgot Your Password?"

    When I fill form with:
      | Email Address | oro |
    Then I should see validation errors:
      | Email Address | This value is not a valid email address. |
    And I should not see validation errors:
      | Email Address | This value should not be blank.                |

    When I fill form with:
      | Email Address |  |
    Then I should see validation errors:
      | Email Address | This value should not be blank. |
    And I should not see validation errors:
      | Email Address | This value is not a valid email address.                |

  Scenario: Verify not existing email address
    Given I am on the homepage
    When I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | nonexisting@example.com |
    And I click "Request"
    Then I should see "If there is a user account associated with nonexisting@example.com you will receive an email with a link to reset your password."

  Scenario: Verify recovery message
    Given I am on the homepage
    When I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | AmandaRCole@example.org |
    And I click "Request"
    Then I should see "If there is a user account associated with AmandaRCole@example.org you will receive an email with a link to reset your password."
    And Email should contains the following:
      | Subject | Reset Account User Password                                          |
      | To      | AmandaRCole@example.org                                              |
      | Body    | Hello, AmandaRCole@example.org!                                      |
      | Body    |  To reset your password - please visit                               |
      | Body    |  customer/user/reset?token=                                          |
