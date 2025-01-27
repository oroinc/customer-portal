@fixture-OroCustomerBundle:CustomerAndCustomerUserWithAddressFixture.yml
@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Edit Customer Address - Select Suggested Address For New Address
  As an Buyer
  I should be able to validate and replace new address with the first suggested

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

  Scenario: Replace entered address with suggested
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
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
      | State           | Colorado        |
      | Zip/Postal Code | 33844           |
    And I uncheck "Frontend Customer Address Billing Checkbox" element
    When I save form
    Then I should see "Confirm Your Address - Primary address"
    When I click "Address Validation Result Form First Suggested Address Radio Storefront"
    And I click on "Use Selected Address Button"
    Then I should see "Customer Address has been saved" flash message
    And I should see following "Customer Company Addresses Grid" grid:
      | Customer Address | City          | State   | Zip/Postal Code | Country       |
      | 801 SCENIC HWY   | HAINES CITY 1 | Florida | 33844-8562      | United States |
