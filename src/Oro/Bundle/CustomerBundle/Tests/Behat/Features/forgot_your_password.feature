@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Forgot your password
  In order to restore password
  As a Customer
  I want to have the forgot password functionality

  Scenario: Verify validation errors
    Given I am on the homepage
    When I click "Sign In"
    And I click "Forgot Your Password?"
    And I fill form with:
      | Email Address | nonexisting@example.com |
    And I press "Request"
    And I wait 1 second
    Then I should see validation errors:
      | Email Address | Email address "nonexisting@example.com" does not exist. |

    When I fill form with:
      | Email Address |  |
    Then I should see validation errors:
      | Email Address | This value should not be blank. |
    And I should not see validation errors:
      | Email Address | Email address "nonexisting@example.com" does not exist. |
      | Email Address | This value is not a valid email address.                |

    When I fill form with:
      | Email Address | oro |
    Then I should see validation errors:
      | Email Address | This value is not a valid email address. |
    And I should not see validation errors:
      | Email Address | Email address "nonexisting@example.com" does not exist. |
      | Email Address | This value should not be blank.                |

  Scenario: Verify recovery message
    Given I fill form with:
      | Email Address | AmandaRCole@example.org |
    And I should not see validation errors:
      | Email Address | This value should not be blank. |
      | Email Address | Email address "nonexisting@example.com" does not exist. |
      | Email Address | This value is not a valid email address.                |
    When I press "Request"
    Then I should see "An email has been sent to ...@example.org. It contains a link you must click to reset your password."
