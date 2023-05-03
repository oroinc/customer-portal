@regression
@fix-BB-20457
@fixture-OroCustomerBundle:CustomersWithOneWithAddressFixture.yml

Feature: Import Customer Addresses With Customer
  In order to add multiple customer addresses at once
  As an Administrator
  I want to be able to import customers with addresses from a CSV file using a provided template

  Scenario: Enable customer addresses import
    Given I login as administrator
    When I go to System / Entities / Entity Management
    And filter Name as is equal to "Customer"
    And I click view Customer in grid
    And I click edit addresses in grid
    When I fill form with:
      | Exclude Column | No  |
      | Export Fields  | All |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Import same customer addresses
    When I go to Customers/Customers
    And I click "Import file"
    And I upload "import_customers/customers_with_same_addresses.csv" file to "Customer Import File"
    And I click "Import file"
    Then Email should contains the following "Errors: 0 processed: 2, read: 2, added: 0, updated: 0, replaced: 2" text

  Scenario Outline: Check addresses
    When I go to Customers/Customers
    And I click view <customer> in grid
    Then I should see "801 Scenic Hwy"

    Examples:
      | customer  |
      | Customer1 |
      | Customer2 |
      | Customer3 |
