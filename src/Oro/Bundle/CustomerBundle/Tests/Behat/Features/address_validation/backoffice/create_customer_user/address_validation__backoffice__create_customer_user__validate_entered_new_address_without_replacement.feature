@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerUserFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Create Customer User - Validate Entered New Address Without Replacement
  As Backend User
  I should see that address validation dialog displayed and
  address details were not changed after address validation on create customer user page

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
    And I click "Create Customer User"
    When I fill "Customer User Form" with:
      | First Name                 | FirstName            |
      | Last Name                  | LastName             |
      | Email Address              | Example1@example.org |
      | Password                   | Example1@example.org |
      | Confirm Password           | Example1@example.org |
      | Customer                   | first customer       |
      | Administrator (Predefined) | true                 |
    And I fill "Customer User Address Form" with:
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
    And I should be on Customer User Create page
    When I click on "Use Selected Address Button"
    Then I should see "Customer user has been saved" flash message
    And I should see "801 Scenic Hwy HAINES CITY FL US 33844"
