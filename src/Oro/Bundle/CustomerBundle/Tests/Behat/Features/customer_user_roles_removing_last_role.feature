@regression
@ticket-BB-7456
@automatically-ticket-tagged
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Customer user roles removing last role
  In order to check user roles permitions
  As an Administrator
  I want to be sure that it is impossible to remove last user role

  Scenario: Customer user role create
    Given I login as administrator
    And I go to Customers/Customer User Roles
    When I click Edit Buyer in grid
    And I click on AmandaRCole@example.org in grid
    And I save and close form
    Then I should see "You cannot remove last role from Amanda Cole"
