@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Removing last role from the customer user
  Regression test for BB-7456

  Scenario: Remove last role from enabled user
    Given I login as administrator
    And I go to Customers/Customer User Roles
    When I click Edit Buyer in grid
    And I click on AmandaRCole@example.org in grid
    And I save and close form
    Then I should see "You cannot remove last role from Amanda Cole"

  Scenario: Remove last role from disabled user
    Given I go to Customer/Customer Users
    And I click Edit AmandaRCole@example.org in grid
    And I uncheck "Enabled"
    And I save and close form
    Then I should see "Customer User has been saved"
    When I go to Customers/Customer User Roles
    And I click Edit Buyer in grid
    And I click on AmandaRCole@example.org in grid
    And I save and close form
    Then I should see "Customer User Role has been saved"

  Scenario: Enable user without roles
    Given I go to Customer/Customer Users
    And I click Edit AmandaRCole@example.org in grid
    And I check "Enabled"
    When I save and close form
    Then I should see "Please select at least one role before you enable the customer user"
