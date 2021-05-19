@regression
@fix-BB-20457
@fixture-OroCustomerBundle:CustomersWithOneWithAddressFixture.yml

Feature: Import Invalid Customer Addresses
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

  Scenario: Import customer same invalid addresses
    When I go to Customers/Customers
    And I click "Import file"
    And I upload "import_customers/customers_with_same_invalid_addresses.csv" file to "Customer Import File"
    And I click "Import file"
    Then Email should contains the following "Errors: 3 processed: 1, read: 2, added: 0, updated: 0, replaced: 1" text
    When I follow "Error log" link from the email
    Then I should see "Error in row #2. addresses[0].firstName: First Name and Last Name or Organization should not be blank."
    And I should see "Error in row #2. addresses[0].lastName: Last Name and First Name or Organization should not be blank."
    And I should see "Error in row #2. addresses[0].organization: Organization or First Name and Last Name should not be blank."
    And I should not see "Notice:"
