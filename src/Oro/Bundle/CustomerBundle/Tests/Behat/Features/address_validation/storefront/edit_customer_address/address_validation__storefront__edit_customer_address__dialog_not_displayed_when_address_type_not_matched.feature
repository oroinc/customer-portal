@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerAndCustomerUserWithAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Edit Customer Address - Dialog Not Displayed When Address Type Not Matched
  As an Buyer
  I should not see address validation when feature address type is not matched

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
      | Validate Shipping Addresses In My Account Use Default | false |
      | Validate Billing Addresses In My Account Use Default  | false |
      | Validate Shipping Addresses In My Account             | true  |
      | Validate Billing Addresses In My Account              | true  |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Unchecked customer address types
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Address Book"
    When I click edit "801 Scenic Hwy" in "Customer Company Addresses Grid"
    And I fill form with:
      | Label           | Primary address |
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I uncheck "Frontend Customer Address Billing Checkbox" element
    And I uncheck "Frontend Customer Address Shipping Checkbox" element
    And I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer Address has been saved" flash message
