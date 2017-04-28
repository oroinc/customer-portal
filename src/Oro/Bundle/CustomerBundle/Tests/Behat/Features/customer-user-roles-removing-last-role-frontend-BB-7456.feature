@ticket-BB-7456
@automatically-ticket-tagged
@fixture-BuyerCustomerFixture.yml
Feature: Removing last role from the customer user on frontend
  Regression test for BB-7456

  Scenario: Customer user role create
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Roles"
    And I click Edit Buyer in grid
    And I fill form with:
      | Role Title | New Buyer Role |
    And I click "Save"
    And I click "Roles"
    When I click Edit New Buyer Role in grid
    And I uncheck AmandaRCole@example.org record in grid
    And click "Save"
    Then I should see "You cannot remove last role from Amanda Cole"
