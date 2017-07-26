@ticket-BB-8879
@selenium-incompatible
@fixture-OroCustomerBundle:MassActionsCustomerAddressFixture.yml
Feature: Mass delete customer user company addresses
  In order to decrease time needed to delete several records at once
  As a customer user
  I want to use mass delete functionality

  Scenario: No records to delete selected
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Address Book"
    And I don't select any record from "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    Then I should see "Please select items to delete." flash message

  Scenario: Delete few manually selected records
    And I click "Account"
    And I click "Address Book"
    And I keep in mind number of records in list in "Customer Company Addresses Grid"
    When I check first 1 records in "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And confirm deletion
    Then I should see "1 address(es) were deleted" flash message
    And the number of records in "Customer Company Addresses Grid" decreased by 1

  Scenario: Cancel Delete records
    And I click "Account"
    And I click "Address Book"
    And I keep in mind number of records in list in "Customer Company Addresses Grid"
    And I check first 1 records in "Customer Company Addresses Grid"
    When I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And cancel deletion
    And the number of records in "Customer Company Addresses Grid" remained the same

  Scenario: Uncheck few records
    And I click "Account"
    And I click "Address Book"
    And I keep in mind number of records in list in "Customer Company Addresses Grid"
    When I check All Visible records in "Customer Company Addresses Grid"
    And I uncheck first 4 records in "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And confirm deletion
    Then the number of records in "Customer Company Addresses Grid" greater than or equal to 4

  Scenario: Select and delete All Visible records
    And I click "Account"
    And I click "Address Book"
    And I keep in mind number of records in list in "Customer Company Addresses Grid"
    When I check All Visible records in "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And confirm deletion
    Then there is no records in "Customer Company Addresses Grid"
