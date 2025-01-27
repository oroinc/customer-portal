@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Edit Customer User Address - Dialog Not Displayed When Feature Disabled
  As an Buyer
  I should not see address validation when feature disabled

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal not displayed
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Address Book"
    When I click edit "801 Scenic Hwy" in "Customer Company User Addresses Grid"
    And I uncheck "Frontend Customer User Address Billing Checkbox" element
    And I uncheck "Frontend Customer User Address Shipping Checkbox" element
    And I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer User Address has been saved" flash message
