@ticket-BB-15402
@ticket-BB-20879
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
Feature: grid views management on datagrids
  In order to manage grid views on front store
  As Frontend User
  I need to create and use grid view on some grid

  Scenario: Create new default grid view with changed filter
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Address Book"
    And I hide filter "State" in "Customer Company Addresses Grid" frontend grid
    When I click grid view list on "Customer Company Addresses Grid" grid
    And I click "Save As New"
    And I set "Test view" as grid view name for "Customer Company Addresses Grid" grid on frontend
    And I mark Set as Default on grid view for "Customer Company Addresses Grid" grid on frontend
    And I click "Add"
    And I should see "View has been successfully created" flash message
    And I click "Flash Message Close Button"
    Then I should see a "Customer Company User Addresses Grid View List" element

  Scenario: Gridview can be renamed few times
    When I click grid view list on "Customer Company Addresses Grid" grid
    And I click "Rename"
    And I set "Test view 01" as grid view name for "Customer Company Addresses Grid" grid on frontend
    And I click "Save"
    And I should see "View has been successfully updated" flash message
    And I click "Flash Message Close Button"
    Then I should see "Test view 01"
    When I click grid view list on "Customer Company Addresses Grid" grid
    And I click "Rename"
    And I set "Test view 02" as grid view name for "Customer Company Addresses Grid" grid on frontend
    And I click "Save"
    And I should see "View has been successfully updated" flash message
    Then I should see "Test view 02"

  Scenario: Create a gridview with existing name
    When I click on empty space
    And I click grid view list on "Customer Company Addresses Grid" grid
    And I click "Save As New"
    And I set "Test view 02" as grid view name for "Customer Company Addresses Grid" grid on frontend
    And I click "Add"
    Then I should see "This name already exists."

  Scenario: Create a gridview with a name that is too long
    When I click on empty space
    And I click grid view list on "Customer Company Addresses Grid" grid
    And I click "Save As New"
    And I set "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa" as grid view name for "Customer Company Addresses Grid" grid on frontend
    And I click "Add"
    Then I should see "This value is too long. It should have 255 characters or less."

  Scenario: Add translation for Saved Views grid action
    Given I login as administrator
    And I enable the existing localizations
    And I go to System / Configuration
    And go to System/Localization/Translations
    And filter Translated Value as is empty
    And filter English translation as contains "Saved Views"
    When I edit "oro_frontend.datagrid_views.saved_views" Translated Value as "Saved Views - Zulu"
    Then I should see following records in grid:
      | Saved Views - Zulu |

  Scenario: Check translations for grid view list
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Address Book"
    And I click "Localization Switcher"
    And I select "Zulu" localization
    When I click grid view list on "Customer Company Addresses Grid" grid
    Then I should see "Saved Views - Zulu"
