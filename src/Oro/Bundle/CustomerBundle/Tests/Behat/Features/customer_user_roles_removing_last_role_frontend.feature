@regression
@ticket-BB-7456
@automatically-ticket-tagged
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Customer user roles removing last role frontend
  ToDo: BAP-16103 Add missing descriptions to the Behat features

  Scenario: Customer user role create
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Roles"
    And I click Edit Buyer in grid
    And I fill form with:
      | Role Title | New Buyer Role |
    And I save form
    And I click "Roles"
    When I click Edit New Buyer Role in grid
    And I uncheck AmandaRCole@example.org record in grid
    And click "Save"
    Then I should see "You cannot remove last role from Amanda Cole"
