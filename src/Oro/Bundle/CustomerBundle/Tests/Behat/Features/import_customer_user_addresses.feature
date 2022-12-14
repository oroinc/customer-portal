@regression
@ticket-BB-14870
@fixture-OroCustomerBundle:CustomerUserAddressForImportExportFixture.yml

Feature: Import Customer User Addresses

  Scenario: Data Template for Customer Addresses
    Given I login as administrator
    And go to Customers/ Customer Users
    When I open "Customer User Addresses" import tab
    And I download "Customer User Addresses" Data Template file
    Then I see Address ID column
    And I see Label column
    And I see Name Prefix column
    And I see First Name column
    And I see Middle Name column
    And I see Last Name column
    And I see Name Suffix column
    And I see Street column
    And I see Street 2 column
    And I see Zip/Postal Code column
    And I see City column
    And I see State column
    And I see State Combined code column
    And I see Country ISO2 code column
    And I see Phone column
    And I see Primary column
    And I see Owner Username column
    And I see Customer User ID column
    And I see Email Address column
    And I see Billing column
    And I see Default Billing column
    And I see Shipping column
    And I see Default Shipping column
    And I see Delete column

  Scenario: Check import of valid addresses
    Given I fill template with data:
      | Label             | Organization | Name Prefix | First Name | Middle Name | Last Name | Name Suffix | Street              | Street 2 | Zip\/Postal Code | City        | State | State Combined code | Country ISO2 code | Address ID | Phone             | Primary | Customer User Id | Email Address            | Owner Username | Billing | Default Billing | Shipping | Default Shipping | Delete |
      | Address A removed | ORO          |             |            |             |           |             | 111 Scenic Hwy      |          | 33844            | Haines City |       | US-FL               | US                |            |                   | 1       |                  | AmandaRCole@example.org  | admin          | 1       | 1               | 1        | 1                | 1      |
      | Address A updated | ORO          |             |            |             |           |             | 222 Scenic Hwy      |          | 33855            | Haines City |       | US-FL               | US                |            |                   | 1       |                  | AmandaRCole@example.org  | admin          | 1       | 0               | 1        | 1                |        |
      | Address A added   | ORO          | Mr.         | John       |             | Doe       | Jr.         | 23400 Caldwell Road |          | 14608            | Rochester   |       | US-NY               | US                |            | (+1) 212 123 4567 | 0       |                  | AmandaRCole@example.org  | admin          | 1       | 1               | 1        | 1                |        |
      | Address B added   | ORO          | Mr.         | John       |             | Doe       | Jr.         | 23400 Caldwell Road |          | 14608            | Rochester   |       | US-NY               | US                |            | (+1) 212 123 4567 | 0       |                  | NancyJSallee@example.org | admin          | 1       | 1               | 1        | 1                |        |
    When I open "Customer User Addresses" import tab
    And I import file
    Then Email should contains the following "Errors: 0 processed: 4, read: 4, added: 2, deleted: 1, updated: 0, replaced: 1" text

  Scenario: Check imported addresses
    When I go to Customers/ Customer Users
    And I click view AmandaRCole@example.org in grid
    Then I should see "Address A updated"
    And I should see "Address A added"
    And I should not see "Address1 amanda"
    And I should not see "Address2 amanda"

    When I go to Customers/ Customer Users
    And I click view NancyJSallee@example.org in grid
    Then I should see "Address B added"
    And I should see "Address1 nancy"
    And I should see "Address2 nancy"
