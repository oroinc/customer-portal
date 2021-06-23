@regression
@ticket-BB-20593
@fixture-OroUserBundle:user.yml
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Customer user activity counter
  In order to know when and how many times customer user was contacted
  As an administrator
  I should be able to see Times Contacted and Last Contacted date for a customer user

  Scenario: Enable Call for Customer User entity
    Given I login as administrator
    And I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "CustomerUser"
    And I click edit CustomerUser in grid
    And I fill form with:
      | Calls | true |
    When I save and close form
    Then I should see "Entity saved" flash message
    When I click update schema
    Then I should see "Schema updated" flash message

  Scenario: Log a call
    When I go to Customers / Customer Users
    And I click view "AmandaRCole@example.org" in grid
    Then I should see "Not contacted yet"
    And I should not see "Last Contacted"
    When click "More actions"
    And I click "Log call"
    And I fill form with:
      | Subject      | Call for AmandaRCole |
      | Phone number | 123456789            |
    When I click "Log call"
    Then I should see "Call saved" flash message
    And I should see "Times Contacted: 1"
    And I should see "Last Contacted"

    When I go to System/ User Management/ Users
    And click View admin in grid
    And I should not see "Last Contacted"
    And I should not see "Times Contacted"
    And I should not see "Not contacted yet"
