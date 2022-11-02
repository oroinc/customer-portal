@fixture-OroCustomerBundle:ActionsInGridLineItemsFixture.yml
Feature: Checked actions in grid line items

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
    # I should have:
    # Company Addresses
    # Address             City      Zip/Postal Code   Country          Customer
    # Company_Street_01   City_01   1234              United States    Acme Company

    # # Scenario: Preconditions
    # I should have:

    # Users
    # First Name         Last Name       Email Address              Customer
    # Amanda             Cole            AmandaRCole@example.org    Acme Company
    # FirstName_1        LastName_1      user_1@example.org         Acme Company
    # FirstName_2        LastName_2      user_2@example.org         Acme Company
    # FirstName_3        LastName_3      user_3@example.org         Acme Company
    # FirstName_4        LastName_4      user_4@example.org         Acme Company
    # FirstName_5        LastName_5      user_5@example.org         Acme Company
    # FirstName_6        LastName_6      user_6@example.org         Acme Company
    # FirstName_7        LastName_7      user_7@example.org         Acme Company
    # FirstName_8        LastName_8      user_8@example.org         Acme Company
    # FirstName_9        LastName_9      user_9@example.org         Acme Company
    # FirstName_10       LastName_10     user_10@example.org        Acme Company

    Scenario: Checked actions in grid line items on Address Book page on "All Company Addresses" grid
      Given I signed in as AmandaRCole@example.org on the store frontend
      And I follow "Account"
      When I click "Address Book"
      Then I should see "1 Total Company Address"
      And I should see following elements in "Customer Company Addresses Grid":
          | FrontendGridActionRefreshButton |
          | FrontendGridActionResetButton   |
          | Frontend Grid Action Filter Button  |
      And I should see following actions for Company_Street_01 in grid:
          | Map    |
          | Edit   |
          | Delete |

    Scenario: Checked actions in grid line items on "All Users" grid
      And I follow "Account"
      And I click "Users"
      And I should see "11 Total Users"
      And I should see "Create User" button
      And I should see following elements in "Frontend Grid":
          | FrontendGridActionRefreshButton |
          | FrontendGridActionResetButton   |
          | Frontend Grid Action Filter Button  |
      And I should see following actions for AmandaRCole@example.org in grid:
          | View |
          | Edit |
      And I should see following actions for user_1@example.org in grid:
          | Disable |
          | View    |
          | Edit    |
          | Delete  |
      And I should not see following elements in "FrontendGridBottomToolbar" for "Frontend Grid":
          | FrontendGridActionRefreshButton |
          | FrontendGridActionResetButton   |
          | Frontend Grid Action Filter Button  |
      And I select 10 from per page list dropdown in "Frontend Grid"
      And I should see following elements in "Frontend Grid":
          | FrontendGridTopToolbar    |
          | FrontendGridBottomToolbar |
      And I go to next page in "Frontend Grid"
      And I should see following grid:
          | First Name   | Email Address       |
          | FirstName_10 | user_10@example.org |
      And I click "Sign Out"
