@ticket-BB-8879
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Mass delete customer users
  In order to decrease time needed to delete several records at once
  As a customer user
  I want to use mass delete functionality

  Scenario: Delete mass action is disabled in Customer User grid for Buyer role
    When I signed in as NancyJSallee@example.org on the store frontend
    And I follow "Account"
    And I click "Users"
    Then I shouldn't see Delete action in "Frontend Grid"

  Scenario: No records to delete selected
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Users"
    And I don't select any record from "Customer Users Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Users Grid"
    Then I should see "Please select items to delete." flash message

  Scenario: Disable few manually selected records except current user
    And I follow "Account"
    And I click "Users"
    And I keep in mind number of records in list in "Customer Users Grid"
    When I check first 2 records in "Customer Users Grid"
    And I click "Disable" link from select all mass action dropdown in "Customer Users Grid"
    And I click "Yes, Disable" in confirmation dialogue
    Then I should see "1 user(s) were disabled" flash message

  Scenario: Enable few manually selected records except current user
    And I follow "Account"
    And I click "Users"
    And I keep in mind number of records in list in "Customer Users Grid"
    When I check first 2 records in "Customer Users Grid"
    And I click "Enable" link from select all mass action dropdown in "Customer Users Grid"
    And I click "Yes, Enable" in confirmation dialogue
    Then I should see "1 user(s) were enabled" flash message

  Scenario: Delete few manually selected records except current user
    And I follow "Account"
    And I click "Users"
    And I keep in mind number of records in list in "Customer Users Grid"
    When I check first 2 records in "Customer Users Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Users Grid"
    And confirm deletion
    Then I should see "1 user(s) were deleted" flash message
    And the number of records in "Customer Users Grid" decreased by 1

  Scenario: Cancel Delete records
    And I follow "Account"
    And I click "Users"
    And I keep in mind number of records in list in "Customer Users Grid"
    And I check first 1 records in "Customer Users Grid"
    When I click "Delete" link from select all mass action dropdown in "Customer Users Grid"
    And cancel deletion
    And the number of records in "Customer Users Grid" remained the same

  Scenario: Uncheck few records
    And I follow "Account"
    And I click "Users"
    And I keep in mind number of records in list in "Customer Users Grid"
    When I check All Visible records in "Customer Users Grid"
    And I uncheck first 4 records in "Customer Users Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Users Grid"
    And confirm deletion
    Then the number of records in "Customer Users Grid" greater than or equal to 4

  Scenario: Select and delete All Visible records
    And I follow "Account"
    And I click "Users"
    And I keep in mind number of records in list in "Customer Users Grid"
    When I check All Visible records in "Customer Users Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Users Grid"
    And confirm deletion
    Then records in "Customer Users Grid" should be 1
