@regression
@ticket-BB-14870
@fixture-OroCustomerBundle:CustomerAddressForImportExportFixture.yml

Feature: Import Customer Addresses

  Scenario: Data Template for Customer Addresses
    Given I login as administrator
    And go to Customers/ Customers
    When I open "Customer Addresses" import tab
    And I download "Customer Addresses" Data Template file
    Then I see Address ID column
    And I see Label column
    And I see Name prefix column
    And I see First name column
    And I see Middle name column
    And I see Last name column
    And I see Name suffix column
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
    And I see Customer Id column
    And I see Customer Name column
    And I see Billing column
    And I see Default Billing column
    And I see Shipping column
    And I see Default Shipping column
    And I see Delete column

  Scenario: Check import of valid addresses
    Given I fill template with data:
      | Label             | Organization | Name prefix | First name | Middle name | Last name | Name suffix | Street              | Street 2 | Zip\/Postal Code | City        | State | State Combined code | Country ISO2 code | Address ID | Phone             | Primary | Customer Id | Customer Name | Owner Username | Billing | Default Billing | Shipping | Default Shipping | Delete |
      | Address A removed | ORO          |             |            |             |           |             | 111 Scenic Hwy      |          | 33844            | Haines City |       | US-FL               | US                |            |                   | 1       |             | Company A     | admin          | 1       | 1               | 1        | 1                | 1      |
      | Address A updated | ORO          |             |            |             |           |             | 222 Scenic Hwy      |          | 33855            | Haines City |       | US-FL               | US                |            |                   | 1       |             | Company A     | admin          | 1       | 0               | 1        | 1                |        |
      | Address A added   | ORO          | Mr.         | John       |             | Doe       | Jr.         | 23400 Caldwell Road |          | 14608            | Rochester   |       | US-NY               | US                |            | (+1) 212 123 4567 | 0       |             | Company A     | admin          | 1       | 1               | 1        | 1                |        |
      | Address B added   | ORO          | Mr.         | John       |             | Doe       | Jr.         | 23400 Caldwell Road |          | 14608            | Rochester   |       | US-NY               | US                |            | (+1) 212 123 4567 | 0       |             | Company B     | admin          | 1       | 1               | 1        | 1                |        |
    When I open "Customer Addresses" import tab
    And I import file
    Then Email should contains the following "Errors: 0 processed: 4, read: 4, added: 2, deleted: 1, updated: 0, replaced: 1" text

  Scenario: Check imported addresses
    When I go to Customers/ Customers
    And I click view Company A in grid
    Then I should see "Address A updated"
    And I should see "Address A added"
    And I should not see "Address1 company_A"
    And I should not see "Address2 company_A"

    When I go to Customers/ Customers
    And I click view Company B in grid
    Then I should see "Address B added"
    And I should see "Address1 company_B"
    And I should see "Address2 company_B"
