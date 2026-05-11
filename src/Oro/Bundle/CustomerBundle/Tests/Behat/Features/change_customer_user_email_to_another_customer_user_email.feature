@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Change customer user email to another customer user email

  Scenario: Customer user email change with enabled change email address feature
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I change configuration options:
      | oro_customer.email_change_verification_enabled | true |
    And I click "Account Dropdown"
    And I click "My Profile"
    And I click "Customer User Profile Edit Email"
    When I fill form with:
      | Password | NancyJSallee@example.org |
      | Email    | AmandaRCole@example.org  |
    And I click "Save"
    Then I should see "This email is already used."
    And I click "Cancel"
    Then I should see "Nancy Sallee"

  Scenario: Customer user email change with disabled change email address feature
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I change configuration options:
      | oro_customer.email_change_verification_enabled | false |
    And I click "Account Dropdown"
    And I click "My Profile"
    And I click "Customer User Profile Edit Email"
    When I fill form with:
      | Password | NancyJSallee@example.org |
      | Email    | AmandaRCole@example.org  |
    And I click "Save"
    Then I should see "This email is already used."
    And I click "Cancel"
    Then I should see "Nancy Sallee"
