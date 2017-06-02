@fixture-BuyerCustomerFixture.yml
Feature: grid views management on datagrids
  As Frontend User
  I need to create and use grid view on some grid

  Scenario: Create new default grid view with changed filter
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Address Book"
    And I hide filter "State" in "Customer Company Addresses Grid" grid
    When I click grid view list on "Customer Company Addresses Grid" grid
    And I click "Save As New"
    And I set "Test view" as grid view name for "Customer Company Addresses Grid" grid on frontend
    And I mark Set as Default on grid view for "Customer Company Addresses Grid" grid on frontend
    And I click "Add"
    Then I should see "View has been successfully created" flash message
    And I should see a "Customer Company User Addresses Grid View List" element
