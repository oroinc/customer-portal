@fixture-OroCustomerBundle:CustomerFixture.yml
Feature: Delete customers
  In order to be able to delete customers only without assigned customer users
  As an administrator
  I need to have a delete button for customers without assigned customer users and no delete button for customers with assigned customer users

  Scenario: Check buttons for customers
    Given I login as administrator
    When I go to Customers / Customers
    Then I should see Assigned in grid
    And I should not see following actions for Assigned in grid:
      | Delete |
    And I should see NonAssigned in grid
    And I should see following actions for NonAssigned in grid:
      | Delete |

  Scenario: Delete customer without assigned customer users
    Given I go to Customers / Customers
    When I keep in mind number of records in list
    And I click Delete NonAssigned in grid
    And I confirm deletion
    Then the number of records decreased by 1
    And I should not see "NonAssigned"
