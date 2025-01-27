@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerAndCustomerUserWithAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Edit Customer Address - Dialog Displayed With Suggests In Select
  As a Buyer User
  I should see that address validation dialog displayed
  with entered address and select that contains all address suggests

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

  Scenario: Validate customer address
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Address Book"
    And I click edit "801 Scenic Hwy" in "Customer Company Addresses Grid"
    When I fill form with:
      | Label           | Primary address           |
      | First name      | Name                      |
      | Last name       | Last name                 |
      | Country         | United States             |
      | Street          | 801 Scenic Hwy short-view |
      | City            | Haines City               |
      | State           | Florida                   |
      | Zip/Postal Code | 33844                     |
    And I uncheck "Frontend Customer Address Billing Checkbox" element
    And I save form
    Then I should see "Confirm Your Address - Primary address"
    When I fill "Address Validation Result Form Storefront" with:
      | Suggested Address Select | 801 SCENIC HWY Second HAINES CITY 2 FL US 33845-8562 |
    And I click on "Use Selected Address Button"
    Then I should see "Customer Address has been saved" flash message
    And I should see following "Customer Company Addresses Grid" grid:
      | Customer Address      | City          | State   | Zip/Postal Code | Country       |
      | 801 SCENIC HWY Second | HAINES CITY 2 | Florida | 33845-8562      | United States |
