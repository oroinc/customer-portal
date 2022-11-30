@ticket-BB-21647
@ticket-BB-21728
@fixture-OroCustomerBundle:CompanyA.yml

Feature: Customer address audit
  In order to manage addresses for Customer
  As an administrator
  I want to be able to see the history of customer address type changes

  Scenario: Make Customer Address entity and its fields auditable
    Given I login as administrator
    And I go to System/Entities/Entity Management
    And filter Name as is equal to "CustomerAddress"
    And click Edit CustomerAddress in grid
    And I fill form with:
      | Auditable | Yes |
    And I save and close form
    Then I should see "Entity saved" flash message
    And click Edit types in grid
    And I fill form with:
      | Auditable | Yes |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Make Address Type entity and its fields auditable
    Given I go to System/Entities/Entity Management
    And filter Name as is equal to "AddressType"
    And click Edit AddressType in grid
    And I fill form with:
      | Auditable | Yes |
    And I save and close form
    Then I should see "Entity saved" flash message
    And click Edit label in grid
    And I fill form with:
      | Auditable | Yes |
    And I save and close form
    Then I should see "Field saved" flash message
    And click Edit name in grid
    And I fill form with:
      | Auditable | Yes |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Creating new customer
    Given I go to Customers / Customers
    And I click "Create Customer"
    And I fill form with:
      | Name            | Test customer     |
      | Country         | Aland Islands     |
      | Street          | Test street       |
      | City            | Test city         |
      | Zip/Postal Code | 111111            |
      | Organization    | Test Organization |
    And I save and close form
    Then I should see "Customer has been saved" flash message

  Scenario: Creating new customer user
    Given I go to Customers / Customer Users
    And I click "Create Customer User"
    And fill form with:
      | First Name        | John                |
      | Last Name         | Doe                 |
      | Email Address     | john.doe@oroinc.com |
      | Customer          | Test customer       |
      | Generate Password | true                |
      | Enabled           | false               |
      | Country           | Aland Islands       |
      | Street            | Test street         |
      | City              | Test city           |
      | Zip/Postal Code   | 111111              |
      | Organization      | Test Organization   |
    And I save and close form
    Then I should see "Customer User has been saved" flash message

  Scenario: Checking billing/shipping and check change history
    Given I go to Customers / Customers
    And I click Edit Test customer in grid
    And I click "First Address Billing Checkbox"
    And I click "First Address Shipping Checkbox"
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values | New values                                                                                                     |
      | Addresses: | Addresses:  Customer Address to Address Type "billing" added Customer Address to Address Type "shipping" added |
    And I close ui dialog

  Scenario: Unchecking billing and check change history
    Given I click "Edit"
    And I click "First Address Billing Checkbox"
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values                                                    | New values |
      | Addresses: Customer Address to Address Type "billing" removed | Addresses: |
    And I close ui dialog

  Scenario: Checking billing/shipping and check change history
    Given I go to System/ Data Audit
    Then I should see following grid containing rows:
      | Action | Version | Entity type   | Data                                                                                                              |
      | Update | 3       | Customer User | Customer:  Customer "Test customer" changed: Addresses: Customer Address to Address Type "billing" types: billing |
