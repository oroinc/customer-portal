@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerAndCustomerUserWithAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Edit Customer Address - Dialog Not Displayed When Feature Not Configured
  As an Buyer
  I should not see address validation when address type is not matched
  with configured in address validation feature system config

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Feature Background
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal displayed
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Address Book"
    When I click edit "801 Scenic Hwy" in "Customer Company Addresses Grid"
    And I save form
    Then I should see "Confirm Your Address - Primary address"
    And I should not see "Customer Address has been saved" flash message
    And I close ui dialog
    And I click "Cancel"

  Scenario: Address Validation disable address types
    Given I proceed as the Admin
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Customer Form" with:
      | Validate Shipping Addresses In My Account Use Default | false |
      | Validate Billing Addresses In My Account Use Default  | false |
      | Validate Shipping Addresses In My Account             | false |
      | Validate Billing Addresses In My Account              | false |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal not displayed
    Given I proceed as the Buyer
    When I click edit "801 Scenic Hwy" in "Customer Company Addresses Grid"
    And I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer Address has been saved" flash message
