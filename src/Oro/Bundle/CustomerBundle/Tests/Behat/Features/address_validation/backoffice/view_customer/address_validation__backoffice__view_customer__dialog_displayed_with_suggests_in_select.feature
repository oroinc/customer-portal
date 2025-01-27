@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer - Dialog Displayed With Suggests In Select
  As an Administrator
  I should see that address validation dialog displayed
  with entered address and select that contains all address suggests

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
    Given I go to Customers/Customers
    When I click view "first customer" in grid
    And click edit Address 1 address
    And I fill form with:
      | First name      | Name                      |
      | Last name       | Last name                 |
      | Country         | United States             |
      | Street          | 801 Scenic Hwy short-view |
      | City            | Haines City               |
      | State           | Florida                   |
      | Organization    | ORO                       |
      | Zip/Postal Code | 33844                     |
    And I click "Save"
    Then I should see "Confirm Your Address - Address 1"
    And I should not see "Address saved" flash message
    When I click "Address Validation Result Form First Suggested Address Radio"
    And I fill "Address Validation Result Form" with:
      | Suggested Address Select | 801 SCENIC HWY Second HAINES CITY 2 FL US 33845-8562 |
    And I click on "Use Selected Address Button"
    Then I should see "Address saved" flash message
    When click edit Address 1 address
    Then form must contains values:
      | Street      | 801 SCENIC HWY Second |
      | City        | HAINES CITY 2         |
      | Postal Code | 33845-8562            |
      | State       | Florida               |
