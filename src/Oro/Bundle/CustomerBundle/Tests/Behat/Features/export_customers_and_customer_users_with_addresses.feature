@ticket-BB-22348
@automatically-ticket-tagged
@fixture-OroCustomerBundle:CustomerAndCustomerUserWithAddressFixture.yml
Feature: Export Customers and Customer Users with addresses
  In order to export list of customer users or customers
  As an Administrator
  I want to have the Export button on the Customers -> Customer page
  I want to have the Export button on the Customer Users -> Customer Users page

  Scenario: Prepare export settings for the CustomerUser and Customer Entities
    Given I login as administrator

    And I go to System / Entities / Entity Management
    And I filter Name as is equal to "CustomerUser"
    And I click view CustomerUser in grid
    And I click edit Addresses in grid
    When I fill form with:
      | Exclude Column | No  |
      | Export Fields  | All |
    And I save and close form
    Then I should see "Field saved" flash message

    And I go to System / Entities / Entity Management
    And I filter Name as is equal to "Customer"
    And I click view Customer in grid
    And I click edit Addresses in grid
    When I fill form with:
      | Exclude Column | No  |
      | Export Fields  | All |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Export Customer Users
    Given I login as administrator
    And I go to Customers/Customer Users
    When I click "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Export performed successfully. 1 customer users were exported. Download" text
    And Exported file for "Customer Users" contains the following data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix | Birthday | Email Address           | Customer Id | Customer Name | Roles 1 Role                | Enabled | Guest | Confirmed | Website Id | Owner Username | Owner Id | Addresses 1 Label | Addresses 1 Organization | Addresses 1 Name Prefix | Addresses 1 First Name | Addresses 1 Middle Name | Addresses 1 Last Name | Addresses 1 Name Suffix | Addresses 1 Street | Addresses 1 Street 2 | Addresses 1 Zip/Postal Code | Addresses 1 City | Addresses 1 State | Addresses 1 State Combined code | Addresses 1 Country ISO2 code | Addresses 1 Address ID | Addresses 1 Phone | Addresses 1 Primary | Addresses 1 Customer User Email Address | Addresses 1 Owner Username |
      | 1  |             | Amanda     |             | Cole      |             |          | AmandaRCole@example.org | 1           | Customer1     | ROLE_FRONTEND_ADMINISTRATOR | 1       | 0     | 1         | 1          |                | 1        | Primary address   | ORO                      |                         | Test                   |                         | Customer              |                         | 801 Scenic Hwy     |                      | 33844                       | Haines City      |                   | US-FL                           | US                            | 1                      |                   | 1                   | AmandaRCole@example.org                 | admin                      |
    And I click Logout in user menu

  Scenario: Export Customers
    Given I login as administrator
    And I go to Customers/Customers
    When I click "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Export performed successfully. 1 customers were exported. Download" text
    And Exported file for "Customers" contains the following data:
      | Id | Name      | Parent Id | Parent Name | Group Name | Owner Id | Owner Username | Tax code | Account Id | VAT Id | Addresses 1 Label | Addresses 1 Organization | Addresses 1 Name prefix | Addresses 1 First name | Addresses 1 Middle name | Addresses 1 Last name | Addresses 1 Name suffix | Addresses 1 Street | Addresses 1 Street 2 | Addresses 1 Zip/Postal Code | Addresses 1 City | Addresses 1 State | Addresses 1 State Combined code | Addresses 1 Country ISO2 code | Addresses 1 Address ID | Addresses 1 Phone | Addresses 1 Primary | Addresses 1 Customer Id | Addresses 1 Customer Name | Addresses 1 Owner Username | Internal rating Id | Payment term Label |
      | 1  | Customer1 |           |             |            | 1        |                |          | 1          |        | Primary address   | ORO                      |                         | Test                   |                         | Customer              |                         | 801 Scenic Hwy     |                      | 33844                       | Haines City      |                   | US-FL                           | US                            | 1                      |                   | 1                   | 1                       | Customer1                 | admin                      |                    |                    |
    And I click Logout in user menu
