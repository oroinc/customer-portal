@fixture-OroCustomerBundle:CustomerFixture.yml
Feature: Delete customers

  Scenario: Delete customer without assigned customer users
    Given I login as administrator
    And I go to Customers / Customers
    Then I should see NonAssigned in grid
    And I keep in mind number of records in list
    When I click Delete NonAssigned in grid
    And I confirm deletion
    Then the number of records decreased by 1
    And I should not see "NonAssigned"

  Scenario: Delete customer with assigned customer users
    Given I go to Customers / Customers
    Then I should see Assigned in grid
    When I click Delete Assigned in grid
    And I confirm deletion
    Then should see "This customer has associated with other entities." flash message
    And I should see "Assigned"
