@ticket-BB-15553
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Frontend customer address default billing shipping
  In order to to manage addresses for Customer User
  As a buyer
  I want to check billing/shipping address automatically when default billing/shipping address checked on Customer's edit page

  Scenario: Checking default billing/shipping for one address does not influence another address's type
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Address Book"
    And I click "New Address"

    When I click "Frontend Address Default Billing Checkbox Label"
    Then The "Frontend Address Billing Checkbox" checkbox should be checked

    When I click "Frontend Address Default Shipping Checkbox Label"
    Then The "Frontend Address Shipping Checkbox" checkbox should be checked

    When I click "Frontend Address Default Billing Checkbox Label"
    Then The "Frontend Address Billing Checkbox" checkbox should be checked

    When I click "Frontend Address Default Shipping Checkbox Label"
    Then The "Frontend Address Shipping Checkbox" checkbox should be checked

    When I click "Frontend Address Default Billing Checkbox Label"
    Then The "Frontend Address Billing Checkbox" checkbox should be checked

    When I click "Frontend Address Default Shipping Checkbox Label"
    Then The "Frontend Address Shipping Checkbox" checkbox should be checked

    When I click "Frontend Address Billing Checkbox Label"
    Then The "Frontend Address Default Billing Checkbox" checkbox should be unchecked

    When I click "Frontend Address Shipping Checkbox Label"
    Then The "Frontend Address Default Shipping Checkbox" checkbox should be unchecked
