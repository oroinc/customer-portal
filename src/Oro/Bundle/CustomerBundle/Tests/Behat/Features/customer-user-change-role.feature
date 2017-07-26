@selenium-incompatible
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Update customer user roles

  Scenario: Customer user able to update his roles
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Users"
    And click Edit NancyJSallee@example.org in grid
    And I check "Buyer"
    And I click "Save"
    Then I should see "Customer User has been saved"

  Scenario: Customer user unable to update his roles
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Users"
    And click Edit AmandaRCole@example.org in grid
    And I should not see "Buyer"
