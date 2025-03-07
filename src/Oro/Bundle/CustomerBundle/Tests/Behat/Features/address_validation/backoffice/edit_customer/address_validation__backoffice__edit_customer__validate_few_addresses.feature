@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer - Validate Few Addresses
  As an Administrator
  I should see two Dialogs because two addresses should be validated

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
      | Validate Shipping Addresses Use Default | false |
      | Validate Billing Addresses Use Default  | false |
      | Validate Shipping Addresses             | true  |
      | Validate Billing Addresses              | true  |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Display one by one Address Validation Dialogs
    Given I go to Customers/Customers
    When I click edit "first customer" in grid
    And I click "Second Address Billing Checkbox"
    And I save form
    Then I should see "Confirm Your Address - Address 2"
    When I click on "Use Selected Address Button"
    Then I should see "Confirm Your Address - Address 1"
    When I click on "Use Selected Address Button"
    Then I should see "Customer has been saved" flash message
