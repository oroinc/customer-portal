@ticket-BB-8879
@ticket-BB-15978
@fixture-OroCustomerBundle:MassActionsCustomerAddressFixture.yml
Feature: Mass delete customer user company addresses
  In order to decrease time needed to delete several records at once
  As a customer user
  I want to use mass delete functionality

  Scenario: Create different window session
    Given sessions active:
      | User  | first_session  |
      | Admin | second_session |

  Scenario: Disallow delete of Customer Address
    Given I proceed as the Admin
    And login as administrator
    And I go to Customers/ Customer User Roles
    And I click edit Administrator in grid
    And select following permissions:
      | Customer Address | Delete:None |
    And I save form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Delete mass action is not available for Customer User when Customer Addresses delete is disallowed
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    When I follow "Account"
    And I click "Address Book"
    Then I should see Delete action in "Customer Company User Addresses Grid"
    And I shouldn't see Delete action in "Customer Company Addresses Grid"

  Scenario: Enable delete of Customer Address
    Given I proceed as the Admin
    And select following permissions:
      | Customer Address | Delete:Сorporate (All Levels) |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message

  Scenario: No records to delete selected
    Given I proceed as the User
    When I reload the page
    Then I should see Delete action in "Customer Company User Addresses Grid"
    And I should see Delete action in "Customer Company Addresses Grid"
    When I don't select any record from "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    Then I should see "Please select items to delete." flash message

  Scenario: Delete few manually selected records
    Given I close all flash messages
    And I keep in mind number of records in list in "Customer Company Addresses Grid"
    When I check first 1 records in "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And confirm deletion
    Then I should see "1 address(es) were deleted" flash message
    And the number of records in "Customer Company Addresses Grid" decreased by 1

  Scenario: Cancel Delete records
    Given I close all flash messages
    And I keep in mind number of records in list in "Customer Company Addresses Grid"
    And I check first 1 records in "Customer Company Addresses Grid"
    When I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And cancel deletion
    Then the number of records in "Customer Company Addresses Grid" remained the same

  Scenario: Uncheck few records
    Given I check All Visible records in "Customer Company Addresses Grid"
    And I uncheck first 4 records in "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And confirm deletion
    Then the number of records in "Customer Company Addresses Grid" greater than or equal to 4

  Scenario: Select and delete All Visible records
    Given I keep in mind number of records in list in "Customer Company Addresses Grid"
    And I close all flash messages
    When I check All Visible records in "Customer Company Addresses Grid"
    And I click "Delete" link from select all mass action dropdown in "Customer Company Addresses Grid"
    And confirm deletion
    Then there is no records in "Customer Company Addresses Grid"
