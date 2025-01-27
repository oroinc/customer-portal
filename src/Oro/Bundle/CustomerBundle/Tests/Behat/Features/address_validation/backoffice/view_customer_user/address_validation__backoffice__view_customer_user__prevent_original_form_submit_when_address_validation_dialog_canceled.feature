@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - View Customer User - Prevent Original Form Submit When Address Validation Dialog Canceled
  As an Administrator
  I should not see continue Quote form submit if I cancel Address Validation dialog

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Display Address Validation Dialog
    Given I go to Customers/Customer Users
    And I click view "AmandaRCole@example.org" in grid
    And I click "New Address"
    And I fill form with:
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I click "New Customer User Address Shipping Checkbox"
    When I click "Save"
    Then I should see "Confirm Your Address"
    And I should not see "Customer user has been saved" flash message
    When I close ui dialog
    Then I should not see "Customer user has been saved" flash message
    When I click "Save"
    Then I should see "Confirm Your Address"
    And I should not see "Customer user has been saved" flash message
    When I click "Address Validation Edit Address Button"
    Then I should not see "Customer user has been saved" flash message
