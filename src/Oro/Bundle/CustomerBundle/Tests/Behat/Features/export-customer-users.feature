@fixture-ExportCustomerUsersFixture.yml
Feature: Export Customer Users
  In order to export list of customer users
  As an Administrator
  I want to have the Export button on the Customers -> Customer Users page

  Scenario: Export Customers
    Given I login as administrator
    And I go to Customers/Customer Users
    When I press "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Export performed successfully. 3 customer users were exported. Download" text
    And Exported file for "Customer Users" contains the following data:
      | ID | First Name | Last Name | Email Address              | Customer Name              | Enabled | Confirmed |
      |  1 | Amanda     | Cole      | AmandaRCole@example.org    | Company A                  | 1       | 1         |
      |  2 | Branda     | Sanborn   | BrandaJSanborn@example.org | Company A                  | 0       | 1         |
      |  3 | Ruth       | Maxwell   | RuthWMaxwell@example.org   | Company A - West Division  | 1       | 0         |
    And I click Logout in user menu
