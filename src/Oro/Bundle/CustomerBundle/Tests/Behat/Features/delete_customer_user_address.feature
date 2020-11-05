@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
Feature: Delete customer user address
  As an Administrator
  I want to be sure that customer user address could be deleted from admin panel
  So I delete customer user address

  Scenario: Delete customer user default address
    Given I login as administrator
    And I go to Customers / Customer Users
    Then I click on AmandaRCole@example.org in grid
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
