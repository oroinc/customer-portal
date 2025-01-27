@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Create Customer User Address - Validate Entered New Address Without Replacement
  As a Buyer
  I should see that address validation dialog displayed and
  address details were not changed after address validation on create customer user page

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Open Customer User Address Edit Page
    Given I signed in as NancyJSallee@example.org on the store frontend
    When I click "Account Dropdown"
    And I click "Address Book"
    Then I should see "New Address"
    When I click "New Address"
    Then I should see "Create Address"

  Scenario: Fill up the customer user address form
    Given I fill form with:
      | Label           | New Customer User Address |
      | Country         | United States             |
      | Street          | 801 Scenic Hwy            |
      | City            | Haines City               |
      | State           | Florida                   |
      | Zip/Postal Code | 12345                     |
      | Shipping        | true                      |
    When I click "Save"
    Then I should see "Confirm Your Address - New Customer User Address"
    When I click on "Use Selected Address Button"
    Then I should see "Customer User Address has been saved" flash message
    And I should see following "Customer Company User Addresses Grid" grid:
      | Customer Address | City        | State   | Zip/Postal Code |
      | 801 Scenic Hwy   | Haines City | Florida | 12345           |
