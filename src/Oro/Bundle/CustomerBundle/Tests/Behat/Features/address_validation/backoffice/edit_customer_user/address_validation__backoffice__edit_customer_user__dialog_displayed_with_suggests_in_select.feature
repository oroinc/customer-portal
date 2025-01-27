@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer User - Dialog Displayed With Suggests In Select
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

  Scenario: Validate customer user address
    Given I go to Customers/Customer Users
    And I click edit "NancyJSallee@example.org" in grid
    When I fill form with:
      | Label           | Primary address           |
      | First name      | Name                      |
      | Last name       | Last name                 |
      | Country         | United States             |
      | Street          | 801 Scenic Hwy short-view |
      | City            | Haines City               |
      | State           | Florida                   |
      | Zip/Postal Code | 33844                     |
    And I click "Customer User First Address Shipping Checkbox"
    And I save form
    Then I should see "Confirm Your Address - Primary address"
    When I click "Address Validation Result Form First Suggested Address Radio"
    And I fill "Address Validation Result Form" with:
      | Suggested Address Select | 801 SCENIC HWY Second HAINES CITY 2 FL US 33845-8562 |
    And I click on "Use Selected Address Button"
    Then I should see "Customer user has been saved" flash message
    And "Customer User Form" must contains values:
      | Street      | 801 SCENIC HWY Second |
      | City        | HAINES CITY 2         |
      | Postal Code | 33845-8562            |
      | State       | Florida               |
