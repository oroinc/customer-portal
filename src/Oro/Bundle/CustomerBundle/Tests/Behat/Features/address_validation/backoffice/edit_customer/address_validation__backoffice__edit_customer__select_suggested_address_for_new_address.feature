@fixture-OroCustomerBundle:CustomerFixture.yml
@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer - Select Suggested Address For New Address
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
    Given I go to Customers/Customers
    And I click edit "NoCustomerUser" in grid
    And I fill form with:
      | Label           | Primary address |
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Colorado        |
      | Zip/Postal Code | 33844           |
    And I click "First Address Shipping Checkbox"
    When I save form
    Then I should see "Confirm Your Address - Primary address"
    When I click "Address Validation Result Form First Suggested Address Radio"
    And I click on "Use Selected Address Button"
    Then I should see "Customer has been saved" flash message
    And "Customer Form" must contains values:
      | First Street      | 801 SCENIC HWY |
      | First City        | HAINES CITY 1  |
      | First Postal Code | 33844-8562     |
      | First State       | Florida        |
