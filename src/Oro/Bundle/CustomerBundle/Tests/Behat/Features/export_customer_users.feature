@ticket-BB-8155
@automatically-ticket-tagged
@fixture-OroCustomerBundle:ExportCustomerUsersFixture.yml
Feature: Export Customer Users
  In order to export list of customer users
  As an Administrator
  I want to have the Export button on the Customers -> Customer Users page

  Scenario: Export Customer Users
    Given I login as administrator
    And I go to Customers/Customer Users
    When I click "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Export performed successfully. 3 customer users were exported. Download" text
    And Exported file for "Customer Users" contains the following data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix | Birthday   | Email Address              | Customer Id | Customer Name             | Roles 1 Role                | Roles 2 Role        | Guest | Enabled | Confirmed | Owner Username | Website Id | Owner Id |
      | 1  | Amanda Pre  | Amanda     | Middle Co   | Cole      | Cole Suff   |            | AmandaRCole@example.org    | 1           | Company A                 | ROLE_FRONTEND_ADMINISTRATOR | ROLE_FRONTEND_BUYER | 0     | 1       | 1         |                | 1          | 1        |
      | 2  |             | Branda     |             | Sanborn   |             | 10/02/1990 | BrandaJSanborn@example.org | 1           | Company A                 | ROLE_FRONTEND_BUYER         |                     | 0     | 0       | 1         |                | 2          | 1        |
      | 3  | Ruth Pre    | Ruth       | Middle Max  | Maxwell   | Ruth Suff   |            | RuthWMaxwell@example.org   | 2           | Company A - West Division | ROLE_FRONTEND_BUYER         |                     | 0     | 1       | 0         |                | 2          | 2        |
    And I click Logout in user menu
