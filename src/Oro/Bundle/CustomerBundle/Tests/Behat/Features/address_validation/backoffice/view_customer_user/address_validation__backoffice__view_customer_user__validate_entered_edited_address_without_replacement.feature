@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer User - Validate Entered Edited Address Without Replacement
  As an Administrator
  I should see that address validation dialog displayed and
  address details were not changed after address validation

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Validate customer user address
    Given I go to Customers/Customer Users
    And I click view "AmandaRCole@example.org" in grid
    And click edit Address 1 address
    When fill form with:
      | Street | 801 Scenic Hwy |
      | City   | Haines City    |
    And I click "Save"
    Then I should see "Confirm Your Address - Address 1"
    And I should not see "Address saved" flash message
    When I click on "Use Selected Address Button"
    And I should see "Address saved" flash message
    When click edit Address 1 address
    Then form must contains values:
      | Street      | 801 Scenic Hwy |
      | City        | Haines City    |
      | Postal Code | 33844          |
      | State       | Florida        |

  Scenario: Submit already validated address
    When I click "Save"
    Then I should not see "Confirm Your Address - Address 1"
    And I should see "Address saved" flash message
