@regression
@fix-BB-20457
@fixture-OroCustomerBundle:CustomerUsersWithAmandaAddressFixture.yml

Feature: Import Customer User Addresses with Customer User
  In order to add multiple customer user addresses at once
  As an Administrator
  I want to be able to import customer users with addresses from a CSV file using a provided template

  Scenario: Enable customer addresses import
    Given I login as administrator
    When I go to System / Entities / Entity Management
    And filter Name as is equal to "CustomerUser"
    And I click view CustomerUser in grid
    And I click edit addresses in grid
    When I fill form with:
      | Exclude Column | No  |
      | Export Fields  | All |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Import same customer addresses
    When I go to Customers/Customer Users
    And I click "Import file"
    And I upload "import_customers/customer_users_with_same_addresses.csv" file to "Customer Import File"
    And I click "Import file"
    Then Email should contains the following "Errors: 0 processed: 2, read: 2, added: 0, updated: 0, replaced: 2" text

  Scenario Outline: Check addresses
    When I go to Customers/Customer Users
    And I click view <customer_user> in grid
    Then I should see "801 Scenic Hwy"

    Examples:
      | customer_user            |
      | AmandaRCole@example.org  |
      | NancyJSallee@example.org |
      | john@example.org         |
