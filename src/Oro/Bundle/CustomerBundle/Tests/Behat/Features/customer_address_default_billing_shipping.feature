@ticket-BB-14525
@ticket-BB-16012
Feature: Customer address default billing shipping
  In order to to manage addresses for Customer
  As an administrator
  I want to check billing/shipping address automatically when default billing/shipping address checked on Customer's edit page

  Scenario: Checking default billing/shipping for one address does not influence another address's type
    Given I login as administrator
    And I go to Customers / Customers
    And I click "Create Customer"
    And I fill form with:
      | Name            | Test customer     |
      | Country         | Aland Islands     |
      | Street          | Test street       |
      | City            | Test city         |
      | Zip/Postal Code | 111111            |
      | Organization    | Test Organization |
    And I click "Add"
    And I click "First Address Default Billing Checkbox"
    Then The "First Address Default Billing Checkbox" checkbox should be checked
    And The "First Address Billing Checkbox" checkbox should be checked
    And The "Second Address Billing Checkbox" checkbox should be unchecked
    And I click "First Address Default Shipping Checkbox"
    Then The "First Address Default Shipping Checkbox" checkbox should be checked
    And The "First Address Shipping Checkbox" checkbox should be checked
    And The "Second Address Shipping Checkbox" checkbox should be unchecked
    And I click "First Address Default Billing Checkbox"
    Then The "First Address Billing Checkbox" checkbox should be checked
    And The "Second Address Billing Checkbox" checkbox should be unchecked
    And I click "First Address Default Shipping Checkbox"
    Then The "First Address Shipping Checkbox" checkbox should be checked
    And The "Second Address Shipping Checkbox" checkbox should be unchecked
    And I save and close form
    Then I should see "Customer has been saved" flash message

  Scenario: Checking billing/shipping address automatically when default billing/shipping address checked for new address in popup
    Given I click "New Address"
    When I fill form with:
      | Name            | Test customer     |
      | Country         | Aland Islands     |
      | Street          | Test street 2     |
      | City            | Test city 2       |
      | Zip/Postal Code | 222222            |
      | Organization    | Test Organization |
    And I click "New Address Default Billing Checkbox"
    Then The "New Address Billing Checkbox" checkbox should be checked
    And I click "New Address Default Shipping Checkbox"
    Then The "New Address Shipping Checkbox" checkbox should be checked
    And click "Save"
    Then I should see "Address saved" flash message
