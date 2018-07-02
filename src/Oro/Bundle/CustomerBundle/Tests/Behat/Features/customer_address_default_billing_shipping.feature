@ticket-BB-14525
Feature: Customer address default billing shipping
  In order to to manage addresses for Customer
  As an administrator
  I want to check billing/shipping address automatically when default billing/shipping address checked on Customer's edit page

  Scenario: Checking default billing/shipping for one address does not influence another address's type
    Given I login as administrator
    And I go to Customers / Customers
    And I click "Create Customer"
    And I fill form with:
      | Name | Test customer |
    And I click "Add"
    And I click "First Address Default Billing Checkbox"
    Then The "First Address Billing Checkbox" checkbox should be checked
    And The "Second Address Billing Checkbox" checkbox should be unchecked
    And I click "First Address Default Shipping Checkbox"
    Then The "First Address Shipping Checkbox" checkbox should be checked
    And The "Second Address Shipping Checkbox" checkbox should be unchecked
    And I click "First Address Default Billing Checkbox"
    Then The "First Address Billing Checkbox" checkbox should be checked
    And The "Second Address Billing Checkbox" checkbox should be unchecked
    And I click "First Address Default Shipping Checkbox"
    Then The "First Address Shipping Checkbox" checkbox should be checked
    And The "Second Address Shipping Checkbox" checkbox should be unchecked
