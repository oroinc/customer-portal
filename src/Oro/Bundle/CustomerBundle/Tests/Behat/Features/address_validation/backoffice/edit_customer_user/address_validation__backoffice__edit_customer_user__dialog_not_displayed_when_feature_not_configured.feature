@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer User - Dialog Not Displayed When Feature Not Configured
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
    Given I go to Customers/Customer Users
    And I click edit "AmandaRCole@example.org" in grid
    When I save form
    Then I should see "Confirm Your Address - Address 1"
    And I close ui dialog
    When I go to Customers/Customer Users
    Then I should see alert with message "You have unsaved changes, are you sure you want to leave this page?"
    And I accept alert

  Scenario: Address Validation disable address types
    Given I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Customer Form" with:
      | Validate Shipping Addresses In Back-Office Use Default | false |
      | Validate Billing Addresses In Back-Office Use Default  | false |
      | Validate Shipping Addresses In Back-Office             | false |
      | Validate Billing Addresses In Back-Office              | false |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal not displayed
    Given I go to Customers/Customer Users
    And I click edit "AmandaRCole@example.org" in grid
    When I save form
    Then I should not see "Confirm Your Address - Address 1"
    And I should see "Customer user has been saved" flash message
