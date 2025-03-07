@regression
@ticket-BB-24869
@fixture-OroCustomerBundle:FrontendGridViewsFixture.yml
Feature: Frontend Grid Views

  #Implement frontend grid views.
  #The functionality should work exactly like in the admin.
  #Also we should remember current grid state (filters, sorters, etc) for each page, and restore it when the user comes back to this page again.

  # Scenario: Preconditions
  # I should have:
  # grid View "All Users"
  # Users
  # First Name         Last Name       Email Address              Customer       Enabled   Confirmed
  # Amanda             Cole            AmandaRCole@example.org    Company A      yes       yes
  # FirstName_1        LastName_1      user_1@example.org         Company A      yes       yes
  # FirstName_2        LastName_2      user_2@example.org         Company A      yes       yes
  # FirstName_3        LastName_3      user_3@example.org         Company A      yes       yes

  Scenario: Create new grid view, checked that settings are saved, delete grid view
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Users"
    When I hide all columns in "Customer Users Grid" except First Name, Last Name, Email Address
    When I filter First Name as contains "FirstName_3"
    When I click grid view list on "Customer Users Grid" grid
    And I click "Save As New"
    And I set "Test_View_1" as grid view name for "Customer Users Grid" grid on frontend
    And I mark Set as Default on grid view for "Customer Users Grid" grid on frontend
    And I click "FrontendGridAddViewButton"
    Then I should see "View has been successfully created" flash message
    And I reload the page
    Then I should see "Test_View_1"
    And I should see following grid:
      |First Name      |Last Name       |Email Address      |
      |FirstName_3     |LastName_3      |user_3@example.org |
    And I shouldn't see "Enabled" column in grid
    And I shouldn't see "Confirmed" column in grid
    And I should see "FrontendGridViewsSaveButton" element inside "Customer Users Grid" element
    And I should see "FrontendGridViewsDiscardButton" element inside "Customer Users Grid" element
    And I should see "FrontendGridViewsOptionsButton" element inside "Customer Users Grid" element

    When I click "FrontendGridViewsOptionsButton"
    Then I should see "Share With Others"
    And I should see "Rename"
    And I should see "Delete"

    When I click "FrontendGridViewsOptionRename"
    Then I should see "FrontendGridViewsInlineRenameForm" element inside "Customer Users Grid" element

    When I fill "FrontendGridViewsInlineRenameForm" with:
      | Grid View Name | Test_View_2 |
    And I click "FrontendGridViewsInlineRenameSubmit"
    Then I should see "View has been successfully updated" flash message

    When I click grid view list on "Customer Users Grid" grid
    And I click "Rename"
    And I set "Test_View_1" as grid view name for "Customer Users Grid" grid on frontend
    And I click "FrontendGridAddViewButton"
    Then I should see "View has been successfully updated" flash message

    When I click "FrontendGridViewsOptionsButton"
    And I click "FrontendGridViewsOptionShare"
    Then I should see "View has been successfully updated" flash message
    And I should see "Unshare"
    And I should see "Rename"
    And I should see "Delete"

    And I click grid view list on "Customer Users Grid" grid
    And I click "Delete"
    And I click "Yes, Delete"
    Then I should see "View has been successfully deleted" flash message
    When I click grid view list on "Customer Users Grid" grid
    Then I should not see "Test_View_1"
    And I click "Account Dropdown"
    And I click "Sign Out"
