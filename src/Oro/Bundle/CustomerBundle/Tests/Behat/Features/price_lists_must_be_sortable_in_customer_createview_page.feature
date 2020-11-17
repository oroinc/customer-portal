@regression
@pricing-storage-combined
@ticket-BB-7811
@automatically-ticket-tagged
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Price lists must be sortable in customer create\view page
  In order to easily rearrange price list priorities
  As an Administrator
  I want to drag-n-drop price lists to sort them in the system configuration, on the customer group and customer edit pages

  Scenario: Changing Price List Priorities In Customer Group
    Given I login as administrator
    And I go to Customers/Customer Groups
    And I click "Create Customer Group"
    Then I should not see "Priority" in "Price List" table
    And I should see drag-n-drop icon present in "Price List" table
    When I fill in "name" with "All Customers"
    And I click "Add Price List"
    And I choose Price List "first price list" in 2 row
    And I choose a Price List "second price list" in 1 row
    And I drag 2 row to the top in "Price List" table
    And I save and close form
    Then I should see "Customer group has been saved" flash message
    And I should see that "first price list" is in 1 row
