@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Create Customer User - Dialog Not Displayed When Original Form Has Errors
  As an Administrator
  I should not see the address validation dialog when the edit customer user form contains validation errors

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

  Scenario: Submit not valid Customer User Form
    Given I go to Customers/Customer Users
    And I click "Create Customer User"
    When I fill "Customer User Address Form" with:
      | Label           | Primary address |
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I click "Customer User First Address Shipping Checkbox"
    And I save and close form
    Then I should see validation errors:
      | First Name    | This value should not be blank. |
      | Last Name     | This value should not be blank. |
    And I should not see "Confirm Your Address - Primary address"
    And I should be on Customer User Create page
