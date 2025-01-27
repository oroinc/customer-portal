@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Storefront - Create Customer Address - Dialog Not Displayed When Original Form Has Errors
  As a Buyer User
  I should see not address validation dialog when origin create customer form contains validation errors

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Submit not valid Customer Form
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Address Book"
    Then I should see "New Company Address"
    When I click "New Company Address"
    Then I should see "Create Address"
    When I click "Save"
    And I should see "This value should not be blank."
    Then I should not see "Confirm Your Address"
