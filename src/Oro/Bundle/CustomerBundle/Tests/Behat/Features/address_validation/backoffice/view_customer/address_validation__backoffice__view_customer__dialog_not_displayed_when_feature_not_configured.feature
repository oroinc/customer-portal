@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer - Dialog Not Displayed When Feature Not Configured
  As an Administrator
  I should not see address validation when address type is not matched
  with configured in address validation feature system config

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal displayed
    Given I go to Customers/Customers
    And I click view "first customer" in grid
    And click edit Address 1 address
    When I click "Save"
    Then I should see "Confirm Your Address - Address 1"

  Scenario: Address Validation disable address types
    Given I close ui dialog
    And I close ui dialog
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Customer Form" with:
      | Validate Shipping Addresses In Back-Office Use Default | false |
      | Validate Billing Addresses In Back-Office Use Default  | false |
      | Validate Shipping Addresses In Back-Office             | false |
      | Validate Billing Addresses In Back-Office              | false |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal not displayed
    Given I go to Customers/Customers
    And I click view "first customer" in grid
    And click edit Address 1 address
    When I click "Save"
    Then I should not see "Confirm Your Address - Address 1"
    And I should see "Address saved" flash message
