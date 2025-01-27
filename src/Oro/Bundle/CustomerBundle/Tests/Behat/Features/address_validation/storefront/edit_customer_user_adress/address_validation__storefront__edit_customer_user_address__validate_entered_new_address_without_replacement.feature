@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Edit Customer User Address - Validate Entered New Address Without Replacement
  As an Buyer
  I should see that address validation dialog displayed and
  address details were not changed after address validation on edit customer user address page

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

  Scenario: Validate customer user address
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Address Book"
    When I click edit "801 Scenic Hwy" in "Customer Company User Addresses Grid"
    And I fill form with:
      | Label       | Primary address |
      | First name  | Name            |
      | Last name   | Last name       |
      | Country     | United States   |
      | Street      | 801 Scenic Hwy  |
      | City        | Haines City     |
      | State       | Florida         |
      | Postal Code | 33844           |
    And I uncheck "Frontend Customer User Address Billing Checkbox" element
    And I save form
    Then I should see "Confirm Your Address - Primary address"
    When I click on "Use Selected Address Button"
    Then I should see "Customer User Address has been saved" flash message
    And I should see following "Customer Company User Addresses Grid" grid:
      | Customer Address | City        | State   | Zip/Postal Code | Country       |
      | 801 Scenic Hwy   | Haines City | Florida | 33844           | United States |

  Scenario: Submit already validated address
    When I click edit "801 Scenic Hwy" in "Customer Company User Addresses Grid"
    And I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer User Address has been saved" flash message
