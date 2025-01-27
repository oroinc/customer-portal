@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Edit Customer User Address - Prevent Original Form Submit When Address Validation Dialog Canceled
  As an Buyer
  I should not see continue Quote form submit if I cancel Address Validation dialog

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

  Scenario: Display Address Validation Dialog
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Address Book"
    When I click edit "801 Scenic Hwy" in "Customer Company User Addresses Grid"
    And I fill form with:
      | First name      | Name           |
      | Last name       | Last name      |
      | Country         | United States  |
      | Street          | 801 Scenic Hwy |
      | City            | Haines City    |
      | State           | Florida        |
      | Zip/Postal Code | 33844          |
    And I uncheck "Frontend Customer User Address Billing Checkbox" element
    And I save form
    Then I should see "Confirm Your Address"
    When I close ui dialog
    Then I should not see "Customer User Address has been saved" flash message
    When I save form
    Then I should see "Confirm Your Address"
    When I click "Edit Address" in modal window
    Then I should not see "Customer User Address has been saved" flash message
