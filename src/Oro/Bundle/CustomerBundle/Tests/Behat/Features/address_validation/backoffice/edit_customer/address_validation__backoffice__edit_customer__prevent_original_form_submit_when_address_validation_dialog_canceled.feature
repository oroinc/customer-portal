@fixture-OroUPSBundle:AddressValidationUpsClient.yml
@fixture-OroCustomerBundle:CustomerFixture.yml
@feature-BB-24101
@regression
@behat-test-env

Feature: Address Validation - Backoffice - Edit Customer - Prevent Original Form Submit When Address Validation Dialog Canceled
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
    Given I go to Customers/Customers
    And I click edit "NoCustomerUser" in grid
    When I fill form with:
      | First name      | Name            |
      | Last name       | Last name       |
      | Country         | United States   |
      | Street          | 801 Scenic Hwy  |
      | City            | Haines City     |
      | State           | Florida         |
      | Zip/Postal Code | 33844           |
    And I click "First Address Shipping Checkbox"
    And I save and close form
    Then I should see "Confirm Your Address"
    When I close ui dialog
    Then I should be on Customer Update page
    When I save and close form
    Then I should see "Confirm Your Address"
    When I click "Edit Address" in modal window
    Then I should be on Customer Update page
