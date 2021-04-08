@ticket-BB-20411
@fixture-OroCustomerBundle:ImportCustomerUsersFixture.yml
@fixture-OroCustomerBundle:CustomerUserRoleSuffixFixture.yml
@regression

Feature: Import Customer Users with Duplicate Emails
  In order to add multiple customer users at once
  As an Administrator
  I want to be able to import customer users from a CSV file that may contain duplicate emails

  Scenario: Data Template for Customer Users
    Given I login as administrator
    And go to Customers/ Customer Users
    And number of records should be 1
    When I download "Customer Users" Data Template file

  Scenario: Import existing by ID Customer User update both rows to same email
    Given I fill template with data:
      | ID | First Name | Last Name | Email Address     | Customer Id | Customer Name | Roles 1 Role                | Website Id | Owner Id | Guest |
      | 1  | Test3      | Test3     | test2@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
      | 1  | Test4      | Test4     | test2@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 2, read: 2, added: 0, updated: 0, replaced: 2" text

    When I reload the page
    Then number of records should be 1
    And I should see following grid containing rows:
      | Customer  | First Name | Last Name | Email Address     |
      | Company A | Test4      | Test4     | test2@example.org |

  Scenario: Import new Customer User with an email same to imported before for Existing by ID Record
    Given I fill template with data:
      | ID | First Name | Last Name | Email Address     | Customer Id | Customer Name | Roles 1 Role                | Website Id | Owner Id | Guest |
      | 1  | Test5      | Test5     | test3@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
      |    | Test6      | Test6     | test3@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 1, read: 2, added: 0, updated: 0, replaced: 1" text

    When I follow "Error log" link from the email
    Then I should see "Error in row #2. This email is already used."

    When I login as administrator
    And go to Customers/ Customer Users
    Then number of records should be 1
    And I should see following grid containing rows:
      | Customer  | First Name | Last Name | Email Address     |
      | Company A | Test5      | Test5     | test3@example.org |

  Scenario: Import new Customer Users, two new rows with same email, no such email in DB
    Given I fill template with data:
      | ID | First Name | Last Name | Email Address           | Customer Id | Customer Name | Roles 1 Role                | Website Id | Owner Id | Guest |
      |    | Amanda     | Cole      | AmandaRCole@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
      |    | Amanda2    | Cole2     | AmandaRCole@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 2, read: 2, added: 1, updated: 0, replaced: 1" text

    When I reload the page
    Then number of records should be 2
    And I should see following grid containing rows:
      | Customer  | First Name | Last Name | Email Address           |
      | Company A | Amanda2    | Cole2     | AmandaRCole@example.org |

  Scenario: Import new Customer Users, two new rows with same email, email present in DB
    Given I fill template with data:
      | ID | First Name | Last Name | Email Address           | Customer Id | Customer Name | Roles 1 Role                | Website Id | Owner Id | Guest |
      |    | Amanda3    | Cole3     | AmandaRCole@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
      |    | Amanda4    | Cole4     | AmandaRCole@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 2, read: 2, added: 0, updated: 0, replaced: 2" text

    When I reload the page
    Then number of records should be 2
    And I should see following grid containing rows:
      | Customer  | First Name | Last Name | Email Address           |
      | Company A | Amanda4    | Cole4     | AmandaRCole@example.org |

  Scenario: Import existing by ID Customer User, email same to present in DB
    Given I fill template with data:
      | ID | First Name | Last Name | Email Address           | Customer Id | Customer Name | Roles 1 Role                | Website Id | Owner Id | Guest |
      | 1  | Amanda5    | Cole5     | AmandaRCole@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 0, read: 1, added: 0, updated: 0, replaced: 0" text
    When I follow "Error log" link from the email
    Then I should see "Error in row #1. Email Address: This email is already used."

    And I login as administrator
    And go to Customers/ Customer Users

  Scenario: Import existing by ID Customer Users update both to same email
    Given I fill template with data:
      | ID | First Name | Last Name | Email Address    | Customer Id | Customer Name | Roles 1 Role                | Website Id | Owner Id | Guest |
      | 1  | Test1      | Test1     | test@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
      | 2  | Test2      | Test2     | test@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 1, read: 2, added: 0, updated: 0, replaced: 1" text
    When I follow "Error log" link from the email
    Then I should see "Error in row #2. This email is already used."

    When I login as administrator
    And go to Customers/ Customer Users
    Then number of records should be 2
    And I should see following grid containing rows:
      | Customer  | First Name | Last Name | Email Address           |
      | Company A | Test1      | Test1     | test@example.org        |
      | Company A | Amanda4    | Cole4     | AmandaRCole@example.org |

  Scenario: Import existing by ID Customer User with an email same to imported before for new Record
    Given I fill template with data:
      | ID | First Name | Last Name | Email Address     | Customer Id | Customer Name | Roles 1 Role                | Website Id | Owner Id | Guest |
      |    | Test3      | Test3     | test4@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
      | 1  | Test4      | Test4     | test4@example.org | 1           | Test          | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1        | 0     |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 1, read: 2, added: 1, updated: 0, replaced: 0" text
    When I follow "Error log" link from the email
    Then I should see "Error in row #2. This email is already used."

    And I login as administrator
    And go to Customers/ Customer Users
    Then number of records should be 3
    And I should see following grid containing rows:
      | Customer  | First Name | Last Name | Email Address           |
      | Company A | Test1      | Test1     | test@example.org        |
      | Company A | Amanda4    | Cole4     | AmandaRCole@example.org |
      | Company A | Test3      | Test3     | test4@example.org       |
