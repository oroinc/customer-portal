@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Removing last role from the customer user on frontend
  Regression test for BB-7456

  Scenario: Remove last role from enabled user
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I follow "Account"
    And I click "Roles"
    And I click Edit Buyer in grid
    And I fill form with:
      | Role Title | New Buyer Role |
    And I click on "Second Save Button"
    And I click "Roles"
    When I click Edit New Buyer Role in grid
    And I uncheck AmandaRCole@example.org record in grid
    And click "Save"
    Then I should see "You cannot remove last role from Amanda Cole"

  Scenario: Remove last role from disabled user
    And I click "Users"
    And I click Edit AmandaRCole@example.org in grid
    And I click on "Enable Customer User checkbox"
    And I click "Save"
    Then I should see "Customer User has been saved"
    And I click "Roles"
    And I click Edit New Buyer Role in grid
    And I uncheck AmandaRCole@example.org record in grid
    And I click "Save"
    Then I should see "Customer User Role has been saved"

  Scenario: Enable user without roles
    Given I click "Users"
    And I click Edit AmandaRCole@example.org in grid
    And I click on "Enable Customer User checkbox"
    And I click "Save"
    Then I should see "Please select at least one role before you enable the customer user"
