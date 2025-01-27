@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer User - Validate Entered New Address Without Replacement
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

  Scenario: Validate customer address
    Given I go to Customers/Customer Users
    And I click view "NancyJSallee@example.org" in grid
    And I click "New Address"
    And I fill form with:
      | Label           | Primary address |
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I click "New Customer User Address Shipping Checkbox"
    When I click "Save"
    Then I should see "Confirm Your Address - Primary address"
    And I should not see "Address saved" flash message
    When I click on "Use Selected Address Button"
    And I should see "Address saved" flash message
    When click edit Primary address
    Then form must contains values:
      | Street      | 801 Scenic Hwy |
      | City        | Haines City    |
      | Postal Code | 33844          |
      | State       | Florida        |
