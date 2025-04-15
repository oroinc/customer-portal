@ticket-BAP-22133
@fixture-OroCustomerBundle:CountriesAndRegions.yml

Feature: Deleted country and region
  In order to select only not deleted country and region

  Scenario: Create different window session
    Given sessions active:
      | Admin  |first_session |

  Scenario: Check if country and region exist during customer creation
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers / Customers
    And I click "Create Customer"
    And should not see the following options for "First Country List" select in form "Customer Form":
      | Country Y     |
    And should see the following options for "First Country List" select in form "Customer Form":
      | Country X    |
    When I fill form with:
      | Country | Country X  |
    And should not see the following options for "First Region List" select in form "Customer Form":
      | Region YY |
    And should see the following options for "First Region List" select in form "Customer Form":
      | Region XX |
    And click "Cancel"

  Scenario: Check deleted region for already saved customer address
    When I go to Customers / Customers
    And I click edit "Customer 1" in grid
    And should see the following options for "First Country List" select in form "Customer Form" pre-filled with "Country X":
      | Country X |
    And should not see the following options for "First Region List" select in form "Customer Form" pre-filled with "Region XY":
      | Region XY |
    And "Customer Form" must contain values:
      | State   | Choose a state... |

  Scenario: Check deleted country for already saved customer address
    When I go to Customers / Customers
    And I click edit "Customer 2" in grid
    And should not see the following options for "First Country List" select in form "Customer Form" pre-filled with "Country Y":
      | Country Y |
    And should not see the following options for "First Region List" select in form "Customer Form" pre-filled with "Region YY":
      | Region YY |
    And "Customer Form" must contain values:
      | Country   | Choose a country... |
