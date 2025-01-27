@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer - Dialog Not Displayed When Feature Disabled
  As an Administrator
  I should not see address validation when feature disabled

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | false  |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal not displayed
    Given I go to Customers/Customers
    And I click view "first customer" in grid
    And click edit Address 1 address
    And I click "Save"
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Address saved" flash message
