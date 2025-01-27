@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer User - Select Suggested Address For New Address
  As an Administrator
  I should be able to validate and replace new address with the first suggested

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Replace entered address with suggested
    Given I go to Customers/Customer Users
    And I click view "AmandaRCole@example.org" in grid
    And click edit Address 1 address
    And I fill form with:
      | First name      | Name           |
      | Last name       | Last name      |
      | Country         | United States  |
      | Street          | 801 Scenic Hwy |
      | City            | Haines City    |
      | State           | Colorado       |
      | Zip/Postal Code | 33844          |
    And I click "Save"
    Then I should see "Confirm Your Address - Address 1"
    When I click "Address Validation Result Form First Suggested Address Radio"
    And I click on "Use Selected Address Button"
    Then I should see "Address saved" flash message
    When click edit Address 1 address
    Then form must contains values:
      | Street      | 801 SCENIC HWY |
      | City        | HAINES CITY 1  |
      | Postal Code | 33844-8562     |
      | State       | Florida        |

