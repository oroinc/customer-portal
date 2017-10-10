@fixture-OroCustomerBundle:CustomerFixture.yml
@ticket-BB-12096
Feature: View and update customers timestamps
  In order to understand when a customer record was created or updated
  As an Administrator
  I want to be able to see "created at" and "updated at" customer timestamps

  Scenario: Check if timestamps columns are hidden by default
    Given I login as administrator
    When I go to Customers / Customers
    Then I should see "Assigned" in grid
    And I shouldn't see "Created at" column in grid
    And I shouldn't see "Updated at" column in grid

  Scenario: Enable customer timestamps on grid
    Given I go to Customers / Customers
    And click "Grid Settings"
    And I click on "Customer Grid Settings Created At"
    And I click on "Customer Grid Settings Updated At"
    And click "Grid Settings"
    Then I should see following grid:
      | Name        | Created At            | Updated at           | Account     |
      | Assigned    | Oct 1, 2017, 12:00 PM | Oct 1, 2017, 1:00 PM | Assigned    |
      | NonAssigned | Oct 2, 2017, 12:00 PM | Oct 2, 2017, 1:00 PM | NonAssigned |

  Scenario: Update Customer and check if updated at change in grid view
    Given I go to Customers / Customers
    And click edit "NonAssigned" in grid
    And I type "NonAssigned updated" in "Name"
    And save and close form
    And I go to Customers / Customers
    And click "Grid Settings"
    And I click on "Customer Grid Settings Created At"
    And I click on "Customer Grid Settings Updated At"
    Then I should see following grid:
      | Name                | Created At            | Account     |
      | Assigned            | Oct 1, 2017, 12:00 PM | Assigned    |
      | NonAssigned updated | Oct 2, 2017, 12:00 PM | NonAssigned |
    And I should not see "Oct 2, 2017, 1:00 PM"
