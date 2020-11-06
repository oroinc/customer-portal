@fixture-OroCustomerBundle:CustomerAddressFixture.yml
Feature: Delete customer address
  In order to check address deletion functionality
  As an Administrator
  I want to delete customer user default address

  Scenario: Delete customer user default address
    Given I login as administrator
    And I go to Customers / Customers
    Then I click on first customer in grid
    And I should see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should see "34500 Capitol Avenue"
    Then I delete 801 Scenic Hwy address
    And click "Yes, Delete"
    And I should not see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should see "34500 Capitol Avenue"
    Then I reload the page
    And I should not see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should see "34500 Capitol Avenue"
