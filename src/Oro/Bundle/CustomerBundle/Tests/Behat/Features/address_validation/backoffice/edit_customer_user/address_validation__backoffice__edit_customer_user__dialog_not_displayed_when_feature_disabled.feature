@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer User - Dialog Not Displayed When Feature Disabled
  As an Administrator
  I should not see address validation when feature disabled

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Address Validation modal not displayed
    Given I go to Customers/Customer Users
    And I click edit "AmandaRCole@example.org" in grid
    When I fill "Customer User Address Form" with:
      | Label | Primary address |
    And I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer user has been saved" flash message
