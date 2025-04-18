@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer User - Dialog Not Displayed When Address Type Not Matched
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
    When I fill "Address Validation Configuration Customer Form" with:
      | Validate Shipping Addresses Use Default | false |
      | Validate Billing Addresses Use Default  | false |
      | Validate Shipping Addresses             | true  |
      | Validate Billing Addresses              | true  |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Unchecked customer address types
    Given I go to Customers/Customer Users
    And I click view "AmandaRCole@example.org" in grid
    And click edit Address 1 address
    And I click "New Customer User Address Shipping Checkbox"
    And I click "New Customer User Address Billing Checkbox"
    When I click "Save"
    Then I should not see "Confirm Your Address - Address 1"
    And I should see "Address saved" flash message

