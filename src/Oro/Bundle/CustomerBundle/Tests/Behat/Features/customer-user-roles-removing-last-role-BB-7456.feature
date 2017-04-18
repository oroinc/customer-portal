@fixture-BuyerCustomerFixture.yml
Feature: Removing last role from the customer user
  Regression test for BB-7456

  Scenario: Customer user role create
    Given I login as administrator
    And I go to Customers/Customer User Roles
    When I click Edit Buyer in grid
    And I click on AmandaRCole@example.org in grid
    And I save and close form
    Then I should see "You cannot remove last role from Amanda Cole"
