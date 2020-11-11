@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
Feature: Delete customer user default address

  Scenario: Delete customer user default address
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    Then I should see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should not see "34500 Capitol Avenue"
    Then I delete 801 Scenic Hwy address
    And I click "Yes, Delete"
    And I should not see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should not see "34500 Capitol Avenue"
    Then I reload the page
    And I should not see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should not see "34500 Capitol Avenue"
