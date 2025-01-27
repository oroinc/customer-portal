@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Create Customer - Validate Entered New Address Without Replacement
  As Backend User
  I should see that address validation dialog displayed and
  address details were not changed after address validation on create customer page

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Validate customer address
    Given I go to Customers/Customers
    When I click "Create Customer"
    And fill "Customer Form" with:
      | Name       | Testing Customer   |
    And I fill form with:
      | Label           | Primary address |
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I click "First Address Shipping Checkbox"
    And I save and close form
    Then I should see "Confirm Your Address - Primary address"
    And I should be on Customer Create page
    When I click on "Use Selected Address Button"
    Then I should see "Customer has been saved" flash message
    When I click "Edit Customer"
    Then "Customer Form" must contains values:
      | First Street      | 801 Scenic Hwy |
      | First City        | Haines City    |
      | First Postal Code | 33844          |
      | First State       | Florida        |

  Scenario: Submit already validated address
    When I save form
    Then I should see "Customer has been saved" flash message
    And I should not see "Confirm Your Address - Primary address"
