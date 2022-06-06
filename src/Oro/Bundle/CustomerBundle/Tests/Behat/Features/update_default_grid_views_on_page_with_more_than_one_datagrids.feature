@regression
@ticket-BB-8155
@automatically-ticket-tagged
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Update default grid views on page with more than one datagrids

  Scenario: Edit create new default grid view
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Address Book"
    When I click grid view list on "Customer Company Addresses Grid" grid
    And I click "Save As New"
    And I set "Test" as grid view name for "Customer Company Addresses Grid" grid on frontend
    And I mark Set as Default on grid view for "Customer Company Addresses Grid" grid on frontend
    And I click "Add"
    And I reload the page
    Then I should see a "Customer Company User Addresses Grid View List" element
