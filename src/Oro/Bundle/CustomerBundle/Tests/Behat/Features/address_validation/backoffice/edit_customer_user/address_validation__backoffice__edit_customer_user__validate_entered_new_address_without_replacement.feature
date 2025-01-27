@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer User - Validate Entered New Address Without Replacement
  As an Administrator
  I should see that address validation dialog displayed and
  address details were not changed after address validation on edit customer user page

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/ Configuration
    And follow "Commerce/Shipping/Address Validation" on configuration sidebar
    When I fill "Address Validation Configuration Form" with:
      | Address Validation Service Use Default | false |
      | Address Validation Service             | UPS   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Validate customer user address
    Given I go to Customers/Customer Users
    And I click edit "AmandaRCole@example.org" in grid
    When I fill "Customer User Address Form" with:
      | Label             | Primary address |
      | First name        | Name            |
      | Last name         | Last name       |
      | First Country     | United States   |
      | First Street      | 801 Scenic Hwy  |
      | First City        | Haines City     |
      | First State       | Florida         |
      | First Postal Code | 33844           |
    And I click "Customer User First Address Shipping Checkbox"
    And I save and close form
    Then I should see "Confirm Your Address - Primary address"
    And I should be on Customer User Update page
    When I click on "Use Selected Address Button"
    Then I should see "Customer user has been saved" flash message
    When I click "Edit Customer User"
    And "Customer User Address Form" must contains values:
      | First Street      | 801 Scenic Hwy |
      | First City        | Haines City    |
      | First Postal Code | 33844          |
      | First State       | Florida        |

  Scenario: Submit already validated address
    When I save form
    Then I should not see "Confirm Your Address - Primary address"
    And I should see "Customer user has been saved" flash message
