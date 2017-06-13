Feature: Checked actions in grid line items
  @ticket-BB-7170

# Pay attention to:
# fonts and spacing
# total records
# pager
#  buttons (per page, refresh, filters, settings)
# actions column
#  bottom pager
#  new record button
# Do not show the second (bottom) pager when there are less than 10 records total available.
#  Actions column - it should be configurable by a theme developer:
#  how to show actions:
#  icons and text labels
#  icons only - https://projects.invisionapp.com/share/9T7EIUAAE#/screens/161067193
#  text only - https://projects.invisionapp.com/share/9T7EIUAAE#/screens/169350232
#  show always (e.g. like here - https://projects.invisionapp.com/share/9T7EIUAAE#/screens/163099324 or https://projects.invisionapp.com/share/9T7EIUAAE#/screens/161067193 ) or show only three dots (like here - https://projects.invisionapp.com/share/9T7EIUAAE#/screens/159170877 )
#  New (create) record button examples:
#  with an icon - https://projects.invisionapp.com/share/9T7EIUAAE#/screens/163099324
#  without an icon - https://projects.invisionapp.com/share/9T7EIUAAE#/screens/161067193
#  PSD https://drive.google.com/file/d/0B3CsM8JeMMssQllxLWNQOUxlSHM/viewUpdate design of frontend grids (all grids under My Account section) according to the provided design - https://projects.invisionapp.com/share/9T7EIUAAE#/screens/159170877

  # # Scenario: Preconditions
#  I should have:
  #Company Addresses
  # Address             City	    Zip/Postal Code	  Country          Customer
  # Company_Street_01   City_01     1234              Aland Islands    Acme Company

    # # Scenario: Preconditions
#  I should have:

  # Users
  # First Name         Last Name	     Email Address              Customer
  # Amanda	           Cole	             AmandaRCole@example.org    Acme Company
  # FirstName_01       LastName_01       user_01@example.org        Acme Company
  # FirstName_02       LastName_02       user_02@example.org        Acme Company
  # FirstName_03       LastName_03       user_03@example.org        Acme Company
  # FirstName_04       LastName_04       user_04@example.org        Acme Company
  # FirstName_05       LastName_05       user_05@example.org        Acme Company
  # FirstName_06       LastName_06       user_06@example.org        Acme Company
  # FirstName_07       LastName_07       user_07@example.org        Acme Company
  # FirstName_08       LastName_08       user_08@example.org        Acme Company
  # FirstName_09       LastName_09       user_09@example.org        Acme Company
  # FirstName_10       LastName_10       user_10@example.org        Acme Company



  Scenario: Checked actions in grid line items on Address Book page on "All Company Addresses" grid
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    When I click "Address Book"
    Then I should see "1 Total Company Addresses"
    And I should see following elements:
      | New Company Address button for All Company Addresses grid|
      | Action System button for All Company Addresses grid      |
      | Action Default button for All Company Addresses grid     |
      | Filter button for All Company Addresses grid             |
    And I should see following actions for Company_Street_01 in grid:
      | edit  |
      | map   |
      | delete|
    And I click "Sign Out"


  Scenario: Checked actions in grid line items on "All Users" grid
    Given I signed in as "AmandaRCole@example.org" on the store frontend
    And I click "Account"
    And I click "Users"
    And I should see "11 Total Users"
    And I should see following elements:
      | Create User button     |
      | Action System Button   |
      | Action Default Button  |
      | Filter button          |
    And I should see following actions for AmandaRCole@example.org in grid:
      | View  |
      | Edit  |
    And I should see following actions for user_01@example.org in grid:
      | Disable  |
      | View     |
      | Edit     |
      | Delete   |
    And I should not see following buttons:
      | Action System Button in bottom toolbar  |
      | Action Default Button in bottom toolbar |
      | Create Filter button in bottom toolbar  |
    And I select 10 from per page list dropdown
    And I should see following elements:
      | Top Pager     |
      | Bottom pager  |
    And I press next page button
    And I should see following grid:
      | email  | user_10@example.org|
    And I click "Sign Out"
