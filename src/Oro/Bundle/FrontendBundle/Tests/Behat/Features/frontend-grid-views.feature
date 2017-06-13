@ticket-BB-7171
@fixture-GridViewsFixture.yml
Feature: Frontend Grid Views
  #Implement frontend grid views.
  #The functionality should work exactly like in the admin.
  #Also we should remember current grid state (filters, sorters, etc) for each page, and restore it when the user comes back to this page again.
  #Design:
  #https://projects.invisionapp.com/share/9T7EIUAAE#/screens/161065160
  #https://projects.invisionapp.com/share/9T7EIUAAE#/screens/175537128
  #PSD file - https://drive.google.com/open?id=0B3CsM8JeMMssQllxLWNQOUxlSHM

  # Scenario: Preconditions
  # I should have:
  # grid View "All Users"
  # Users
  # First Name         Last Name	     Email Address              Customer       Enabled   Confirmed
  # Amanda	           Cole	           AmandaRCole@example.org    Company A      yes       yes
  # FirstName_01       LastName_01     user_01@example.org        Company A      yes       yes
  # FirstName_02       LastName_02     user_02@example.org        Company A      yes       yes
  # FirstName_03       LastName_03     user_03@example.org        Company A      yes       yes

  Scenario: Create new grid view, checked that settings are saved, delete grid view
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Users"
    And I click "Manage Grid"
    # When I hide all columns in grid except First Name, Last Name, Email Address
    When I filter Filter By First Name as contains "FirstName_03"
    When I click grid view list on "Customer User Grid" grid
    And I click "Save As New"
    And I set "Test_View_1" as grid view name for "Customer User Grid" grid on frontend
    And I mark Set as Default on grid view for "Customer User Grid" grid on frontend
    And I click "Add"
    Then I should see "View has been successfully created" flash message
    And I reload the page
    Then I should see "Test_View_1"
    And I should see following grid:
      |First Name      |Last Name       |Email Address          |
      |FirstName_03    |LastName_03     |user_03@example.org    |
    # And I should not see "Enabled"
    # And I should not see "Confirmed"

    And I click grid view list on "Customer User Grid" grid
    And I click "Delete"
    And I press "Yes, Delete"
    Then I should see "View has been successfully deleted" flash message
    When I click grid view list on "Customer User Grid" grid
    Then I should not see "Test_View_1"
    And I click "Sign Out"
