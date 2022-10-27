@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Update customer user roles
  As an Administrator
  I want be sure
  That user without permission could not edit own roles

  Scenario: Customer user able to update his roles
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |
    And I proceed as the User
    And I signed in as NancyJSallee@example.org on the store frontend
    And I follow "Account"
    And I click "Users"
    And click Edit NancyJSallee@example.org in grid
    And I fill form with:
      | Buyer (Predefined) | true |
    And I click "Save"
    Then I should see "Customer User has been saved"

  Scenario: Check customer user info
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers/Customer Users
    And click on NancyJSallee@example.org in grid
    And I should see Customer User with:
      | Roles   | Administrator Buyer |
      | Website | Default|

  Scenario: Customer user unable to update his roles
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Users"
    And I should not see following actions for AmandaRCole@example.org in grid:
      | Edit |
