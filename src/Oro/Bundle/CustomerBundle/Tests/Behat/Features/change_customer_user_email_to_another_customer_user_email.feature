@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Change customer user email to another customer user email

  Scenario: Customer user email change
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "My Profile"
    And I click "Edit"
    And I fill form with:
      | Email | AmandaRCole@example.org |
    And I click "Save"
    Then I should see "This email is already used."
    And I click "Cancel"
    Then I should see "Nancy Sallee"
