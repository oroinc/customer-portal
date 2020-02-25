@regression
@fixture-OroCustomerBundle:CustomerUserAmandaRCole.yml
Feature: Hide Is Primary Address checkbox for first address
  In order to improve user experience
  As a front store user
  I should not see Primary address checkbox when creating first address

  Scenario: Check Is Primary checkbox is not shown when creating first customer address
    Given I login as administrator
    And I go to Customers/Customer User Roles
    And I click edit Buyer in grid
    And select following permissions:
      | Customer Address      | Create:Department (Same Level) |
      | Customer User Address | Create:Department (Same Level) |
    And I save and close form
    Then I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Address Book"
    And I click "New Company Address"
    Then I should not see "Is Primary Address Checkbox" element inside "Create Address Form" element
    And I fill form with:
      | Street          | Main street   |
      | City            | Springfield   |
      | Country         | United States |
      | State           | Alabama       |
      | Zip/Postal Code | 123456        |
    And I save form
    Then I should see "Customer Address has been saved" flash message
    And I click "New Company Address"
    Then I should see "Is Primary Address Checkbox" element inside "Create Address Form" element
    And I click "Cancel"

  Scenario: Check Is Primary checkbox is not shown when creating first customer user address
    And I click "New Address"
    Then I should not see "Is Primary Address Checkbox" element inside "Create Address Form" element
    And I fill form with:
      | Street          | Second street   |
      | City            | Springfield   |
      | Country         | United States |
      | State           | Alabama       |
      | Zip/Postal Code | 123456        |
    And I save form
    Then I should see "Customer User Address has been saved" flash message
    And I click "New Company Address"
    Then I should see "Is Primary Address Checkbox" element inside "Create Address Form" element
