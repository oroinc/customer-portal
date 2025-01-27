@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer - Dialog Not Displayed When Address Type Not Matched
  As an Administrator
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
      | Validate Shipping Addresses In Back-Office Use Default | false |
      | Validate Billing Addresses In Back-Office Use Default  | false |
      | Validate Shipping Addresses In Back-Office             | true  |
      | Validate Billing Addresses In Back-Office              | true  |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Unchecked customer address types
    Given I go to Customers/Customers
    And I click edit "NoCustomerUser" in grid
    When I fill form with:
      | Label           | Primary address |
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer has been saved" flash message

