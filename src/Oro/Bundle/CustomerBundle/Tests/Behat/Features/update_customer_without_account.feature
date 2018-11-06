@ticket-BB-15141
@fixture-OroCustomerBundle:AccountFixture.yml
Feature: Update customer without account
  In order to have ability edit customer
  As administrator
  I need to be able to edit customer without account

  Scenario: Update customer without account
    Given I login as administrator
    And I go to Customers/Customers
    And click "Create Customer"
    And I fill in "Name" with "John Smith"
    When I save and close form
    Then I should see "Customer has been saved" flash message
    When I go to Customers/Accounts
    Then I should see John Smith in grid
    When I click delete John Smith in grid
    And I confirm deletion
    Then I should see "Item deleted" flash message
    When I go to Customers/Customers
    Then I should see John Smith in grid
    When I click edit John Smith in grid
    And I fill in "Account" with "Andrea Joe"
    And I save and close form
    Then I should see "Customer has been saved" flash message
