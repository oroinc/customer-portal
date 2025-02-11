@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Create Customer - Dialog Not Displayed When Original Form Has Errors
  As an Administrator
  I should not see the address validation dialog when the edit customer form contains validation errors

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message
    When I fill "Address Validation Configuration Customer Form" with:
      | Validate Shipping Addresses Use Default | false |
      | Validate Billing Addresses Use Default  | false |
      | Validate Shipping Addresses             | true  |
      | Validate Billing Addresses              | true  |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Submit not valid Customer Form
    Given I go to Customers/Customers
    And I click "Create Customer"
    When I fill form with:
      | Label           | Primary address |
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I click "First Address Billing Checkbox"
    And I save form
    Then I should see validation errors:
      | Name      | This value should not be blank. |
    And I should not see "Confirm Your Address - Primary address"
    And I should not see "Customer has been saved" flash message
