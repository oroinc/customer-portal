@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer - Validate Entered New Address Without Replacement
  As an Administrator
  I should see that address validation dialog displayed and
  address details were not changed after address validation on edit customer page

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
    And I click edit "NoCustomerUser" in grid
    When I fill form with:
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
    And I should be on Customer Update page
    When I click on "Use Selected Address Button"
    Then I should see "Customer has been saved" flash message
    When I click "Edit Customer"
    And "Customer Form" must contains values:
      | First Street      | 801 Scenic Hwy |
      | First City        | Haines City    |
      | First Postal Code | 33844          |
      | First State       | Florida        |

  Scenario: Submit already validated address
    When I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer has been saved" flash message
