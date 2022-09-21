@ticket-BB-21647
@fixture-OroCustomerBundle:CompanyA.yml

Feature: Customer user address audit
  In order to manage addresses for CustomerUser
  As an administrator
  I want to be able to see the history of customer user address type changes

  Scenario: Make Customer User Address entity and its fields auditable
    Given I login as administrator
    Given I go to System/Entities/Entity Management
    And filter Name as is equal to "CustomerUserAddress"
    And click Edit CustomerUserAddress in grid
    And I fill form with:
      | Auditable | Yes |
    And I save and close form
    Then I should see "Entity saved" flash message
    And click Edit types in grid
    And I fill form with:
      | Auditable | Yes |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Creating new customer user
    Given I go to Customers / Customer Users
    And I click "Create Customer User"
    And fill form with:
      | First Name        | John                |
      | Last Name         | Doe                 |
      | Email Address     | john.doe@oroinc.com |
      | Customer          | Company A           |
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
    Given I click "Edit"
    And I click "Customer User First Address Billing Checkbox"
    And I click "Customer User First Address Shipping Checkbox"
    And I save and close form
    Then I should see "Customer User has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values | New values                                                                                                               |
      | Addresses: | Addresses:  Customer User Address to Address Type "billing" added Customer User Address to Address Type "shipping" added |
    And I close ui dialog

  Scenario: Unchecking billing and check change history
    Given I click "Edit"
    And I click "Customer User First Address Billing Checkbox"
    And I save and close form
    Then I should see "Customer User has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values                                                         | New values |
      | Addresses: Customer User Address to Address Type "billing" removed | Addresses: |
