@fixture-OroCustomerBundle:ImportCustomerUsersFixture.yml
@fixture-OroCustomerBundle:CustomerUserRoleSuffixFixture.yml
@regression

Feature: Import Customer Users
  In order to add multiple customer users at once
  As an Administrator
  I want to be able to import customer users from a CSV file using a provided template

  Scenario: Data Template for Customer Users
    Given I login as administrator
    And go to Customers/ Customer Users
    And number of records should be 1
    When I download "Customer Users" Data Template file
    Then I see ID column
    And I see Name Prefix column
    And I see First Name column
    And I see Middle Name column
    And I see Last Name column
    And I see Name Suffix column
    And I see Birthday column
    And I see Email Address column
    And I see Customer Id column
    And I see Customer Name column
    And I see Roles 1 Role column
    And I see Roles 2 Role column
    And I see Website Id column
    And I see Enabled column
    And I see Confirmed column
    And I see Owner Id column

  Scenario: Import new Customer Users
    Given I fill template with data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix | Birthday   | Email Address              | Customer Id | Customer Name             | Roles 1 Role                | Roles 2 Role                     | Website Id | Enabled | Confirmed | Owner Id | Guest |
      |    | Amanda Pre  | Amanda     | Middle Co   | Cole      | Cole Suff   | 10/21/1980 | AmandaRCole@example.org    | 1           | Test                      | ROLE_FRONTEND_ADMINISTRATOR | ROLE_FRONTEND_BUYER              | 1          | 1       | 1         | 1        | No    |
      |    |             | Branda     |             | Sanborn   |             |            | BrandaJSanborn@example.org | 1           | Company A                 | ROLE_FRONTEND_BUYER         |                                  | 2          | 0       | 1         |          | No    |
      |    | Ruth Pre    | Ruth       | Middle Max  | Maxwell   | Ruth Suff   | 11/06/1988 | RuthWMaxwell@example.org   | 2           | Company A - West Division | ROLE_FRONTEND_BUYER         | ROLE_FRONTEND_TEST_5ec79db4eafcd | 2          | 1       | 0         | 2        | No    |
    When I import file
    And Email should contains the following "Errors: 0 processed: 3, read: 3, added: 3, updated: 0, replaced: 0" text
    And Email should not contains the following:
      | Body | Error Log |
    And I reload the page
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda       | Cole      | AmandaRCole@example.org    | Yes     | Yes       |
      | Company A                 | Branda       | Sanborn   | BrandaJSanborn@example.org | No      | Yes       |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | No        |
    And number of records should be 4
    And I click view "AmandaRCole@example.org" in grid
    And I should see "Owner: John Doe"
    And I should see Customer User with:
      | Name Prefix | Amanda Pre          |
      | Middle Name | Middle Co           |
      | Name Suffix | Cole Suff           |
      | Birthday    | Oct 21, 1980        |
      | Roles       | Administrator Buyer |
      | Website     | Default             |
    And go to Customers/ Customer Users
    And I click view "BrandaJSanborn@example.org" in grid
    And I should see "Owner: John Doe"
    And I should see Customer User with:
      | Name Prefix | N/A    |
      | Middle Name | N/A    |
      | Name Suffix | N/A    |
      | Birthday    | N/A    |
      | Roles       | Buyer  |
      | Website     | Second |
    And go to Customers/ Customer Users
    And I click view "RuthWMaxwell@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | Ruth Pre    |
      | Middle Name | Middle Max  |
      | Name Suffix | Ruth Suff   |
      | Birthday    | Nov 6, 1988 |
      | Roles       | Buyer Test  |
      | Website     | Second      |

  Scenario: Import Customer User with invalid email
    Given I go to Customers/ Customer Users
    When I download "Customer Users" Data Template file
    And fill template with data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix | Birthday   | Email Address              | Customer Id | Roles 1 Role                | Website Id | Enabled | Confirmed | Owner Id |
      |    | NewUser     | NewFirst   | NewMiddle   | NewLast   | NewUser     | 10/21/2010 | just"not"right@example.com | 1           | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1       | 1         | 1        |
    And import file
    Then Email should contains the following "Errors: 1 processed: 0, read: 1, added: 0, updated: 0, replaced: 0" text
    And follow "Error log" link from the email
    And should see "Error in row #1. Email Address: This value is not a valid email address."

  Scenario: Enable Case-Insensitive Email option
    Given I am on dashboard
    And go to System/Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And I check "Case-Insensitive Email Addresses"
    When I save form
    Then I should see "Configuration saved" flash message

  Scenario: Update Customer Users
    Given I go to Customers/ Customer Users
    And I fill template with data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix | Birthday   | Email Address              | Customer Id | Roles 1 Role                | Roles 2 Role | Website Id | Enabled | Confirmed | Owner Id |
      | 2  | Amanda Pre  | Amanda_up  | Middle Co   | Cole      | Cole Suff   | 10/21/1980 |                            | 1           | ROLE_FRONTEND_ADMINISTRATOR |              | 1          | 0       | 1         | 2        |
      | 3  | New Prefix  | Branda     |             | Sanborn   |             | 05/11/1985 | BrandaJSanborn@example.org | 2           | ROLE_FRONTEND_ADMINISTRATOR |              | 1          | 1       | 0         | 1        |
      |    | None        | None       | None        | None      | None        | 11/06/1990 | ruthwmaxwell@example.org   | 2           | ROLE_FRONTEND_BUYER         |              | 2          | 1       | 1         | 2        |
      | 4  | Ruth Pre    | Ruth       | Middle XXX  | Maxwell   | Ruth XXX    | 11/06/1990 | RuthWMaxwell@example.org   | 2           | ROLE_FRONTEND_BUYER         |              | 2          | 1       | 1         | 2        |
    When I import file
    And Email should contains the following "Errors: 0 processed: 4, read: 4, added: 0, updated: 0, replaced: 4" text
    And I reload the page
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda_up    | Cole      | AmandaRCole@example.org    | No      | Yes       |
      | Company A - West Division | Branda       | Sanborn   | BrandaJSanborn@example.org | Yes     | No        |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | Yes       |
    And number of records should be 4
    And I click view "AmandaRCole@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | Amanda Pre    |
      | Middle Name | Middle Co     |
      | Name Suffix | Cole Suff     |
      | Birthday    | Oct 21, 1980  |
      | Roles       | Administrator |
      | Website     | Default       |
    And go to Customers/ Customer Users
    And I click view "BrandaJSanborn@example.org" in grid
    And I should see "Owner: John Doe"
    And I should see Customer User with:
      | Name Prefix | New Prefix    |
      | Middle Name | N/A           |
      | Name Suffix | N/A           |
      | Birthday    | May 11, 1985  |
      | Roles       | Administrator |
      | Website     | Default       |
    And go to Customers/ Customer Users
    And I click view "RuthWMaxwell@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | Ruth Pre    |
      | Middle Name | Middle XXX  |
      | Name Suffix | Ruth XXX    |
      | Birthday    | Nov 6, 1990 |
      | Roles       | Buyer       |
      | Website     | Second      |

  Scenario: Disable Case-Insensitive Email option
    Given I go to System/Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And I uncheck "Case-Insensitive Email Addresses"
    When I save form
    Then I should see "Configuration saved" flash message

  Scenario: Export - Import Customer Users
    Given I go to Customers/ Customer Users
    And I click "Export"
    And I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Export performed successfully. 4 customer users were exported" text
    When I import exported file
    And Email should contains the following "Errors: 0 processed: 4, read: 4, added: 0, updated: 0, replaced: 4" text
    And I reload the page
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda_up    | Cole      | AmandaRCole@example.org    | No      | Yes       |
      | Company A - West Division | Branda       | Sanborn   | BrandaJSanborn@example.org | Yes     | No        |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | Yes       |
    And number of records should be 4
    And I click view "AmandaRCole@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | Amanda Pre    |
      | Middle Name | Middle Co     |
      | Name Suffix | Cole Suff     |
      | Birthday    | Oct 21, 1980  |
      | Roles       | Administrator |
      | Website     | Default       |
    And go to Customers/ Customer Users
    And I click view "BrandaJSanborn@example.org" in grid
    And I should see "Owner: John Doe"
    And I should see Customer User with:
      | Name Prefix | New Prefix    |
      | Middle Name | N/A           |
      | Name Suffix | N/A           |
      | Birthday    | May 11, 1985  |
      | Roles       | Administrator |
      | Website     | Default       |
    And go to Customers/ Customer Users
    And I click view "RuthWMaxwell@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | Ruth Pre    |
      | Middle Name | Middle XXX  |
      | Name Suffix | Ruth XXX    |
      | Birthday    | Nov 6, 1990 |
      | Roles       | Buyer       |
      | Website     | Second      |
    And I click Logout in user menu

  Scenario: Import new Customer Users by user without "Create" permission
    Given user has following permissions
      | Assign | Customer User      | Global |
      | Create | Customer User      | None   |
      | Edit   | Customer User      | Global |
      | Edit   | Customer           | Global |
      | Edit   | Customer User Role | Global |
      | Edit   | Website            | Global |
      | Edit   | User               | Global |
    And user has following entity permissions enabled
      | Import Entity Records |
    And I login to dashboard as "userWithoutAssign1" user
    And go to Customers/ Customer Users
    And number of records should be 4
    And I download "Customer Users" Data Template file
    And I fill template with data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix  | Birthday   | Email Address       | Customer Id | Roles 1 Role                | Website Id | Enabled | Confirmed | Owner Id |
      |    | NewUser Pre | NewFirst   | NewMiddle   | NewLast   | NewUser Suff | 10/21/1980 | NewUser@example.org | 1           | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1       | 1         | 1        |
    When I import file
    And Email should contains the following "Errors: 1 processed: 0, read: 1, added: 0, updated: 0, replaced: 0" text
    And I reload the page
    And number of records should be 4
    And I click Logout in user menu

  Scenario: Import new Customer Users by user with "Create" permission but not admin
    And user has following permissions
      | Create | Customer User | Global |
    And I login to dashboard as "userWithAssign1" user
    And go to Customers/ Customer Users
    And I download "Customer Users" Data Template file
    And I fill template with data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix  | Birthday   | Email Address       | Customer Id | Roles 1 Role                | Website Id | Enabled | Confirmed | Owner Id | Guest |
      |    | NewUser Pre | NewFirst   | NewMiddle   | NewLast   | NewUser Suff | 10/21/1980 | NewUser@example.org | 1           | ROLE_FRONTEND_ADMINISTRATOR | 1          | 1       | 1         | 2        | No    |
    When I import file
    And Email should contains the following "Errors: 0 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text
    And I reload the page
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda_up    | Cole      | AmandaRCole@example.org    | No      | Yes       |
      | Company A - West Division | Branda       | Sanborn   | BrandaJSanborn@example.org | Yes     | No        |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | Yes       |
      | Company A                 | NewFirst     | NewLast   | NewUser@example.org        | Yes     | Yes       |
    And I click view "NewUser@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | NewUser Pre   |
      | Middle Name | NewMiddle     |
      | Name Suffix | NewUser Suff  |
      | Birthday    | Oct 21, 1980  |
      | Roles       | Administrator |
      | Website     | Default       |
    And I click Logout in user menu

  Scenario: Update Customer User with only specific columns
    Given I login as administrator
    And go to Customers/ Customer Users
    And I fill template with data:
      | ID | Customer Id | Website Id |
      | 5  | 2           | 2          |
    When I import file
    And Email should contains the following "Errors: 0 processed: 1, read: 1, added: 0, updated: 0, replaced: 1" text
    And I reload the page
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda_up    | Cole      | AmandaRCole@example.org    | No      | Yes       |
      | Company A - West Division | Branda       | Sanborn   | BrandaJSanborn@example.org | Yes     | No        |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | Yes       |
      | Company A - West Division | NewFirst     | NewLast   | NewUser@example.org        | Yes     | Yes       |
    And I click view "NewUser@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | NewUser Pre   |
      | Middle Name | NewMiddle     |
      | Name Suffix | NewUser Suff  |
      | Birthday    | Oct 21, 1980  |
      | Roles       | Administrator |
      | Website     | Second        |

  Scenario: Update Customer Users emails
    Given go to Customers/ Customer Users
    And I fill template with data:
      | ID | Email Address          |
      | 5  | NewUser_UP@example.org |
    When I import file
    And Email should contains the following "Errors: 0 processed: 1, read: 1, added: 0, updated: 0, replaced: 1" text
    And I reload the page
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda_up    | Cole      | AmandaRCole@example.org    | No      | Yes       |
      | Company A - West Division | Branda       | Sanborn   | BrandaJSanborn@example.org | Yes     | No        |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | Yes       |
      | Company A - West Division | NewFirst     | NewLast   | NewUser_UP@example.org     | Yes     | Yes       |
    And I click view "NewUser_UP@example.org" in grid
    And I should see "Owner: New Owner"
    And I should see Customer User with:
      | Name Prefix | NewUser Pre   |
      | Middle Name | NewMiddle     |
      | Name Suffix | NewUser Suff  |
      | Birthday    | Oct 21, 1980  |
      | Roles       | Administrator |
      | Website     | Second        |

  Scenario: Update Customer Users with wrong role and adding a new customer
    Given go to Customers/ Customer Users
    And I fill template with data:
      | ID | Name Prefix | First Name | Middle Name | Last Name | Name Suffix | Birthday   | Email Address           | Customer Id | Roles 1 Role                 | Roles 2 Role | Website Id | Enabled | Confirmed | Owner Id | Guest |
      | 2  | Amanda Pre  | Amanda_up  | Middle Co   | Cole      | Cole Suff   | 10/21/1980 | AmandaRCole@example.org | 1           | ROLE_FRONTEND_ADMINISTRATOR2 |              | 1          | 0       | 1         | 1        | No    |
      |    | testtest    | test       | test        | test      | Cole Suff   | 10/21/1986 | tester@example.org      | 1           | ROLE_FRONTEND_ADMINISTRATOR  |              | 1          | 0       | 1         | 2        | No    |
    When I import file
    And reload the page
    And Email should contains the following "Errors: 1 processed: 2, read: 2, added: 1, updated: 0, replaced: 1" text
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda_up    | Cole      | AmandaRCole@example.org    | No      | Yes       |
      | Company A - West Division | Branda       | Sanborn   | BrandaJSanborn@example.org | Yes     | No        |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | Yes       |
      | Company A - West Division | NewFirst     | NewLast   | NewUser_UP@example.org     | Yes     | Yes       |
      | Company A                 | test         | test      | tester@example.org         | No      | Yes       |

  Scenario: Update Customer Users data with BOM import file
    Given go to Customers/ Customer Users
    And I fill template with data:
      | ID | First Name | Middle Name      | Email Address              |
      | 2  | Amanda_upd | AmandaMiddleName | NewAmandaRCole@example.org |
    And I save import file with BOM
    When I import file
    And Email should contains the following "Errors: 0 processed: 1, read: 1, added: 0, updated: 0, replaced: 1" text
    And I reload the page
    And I should see following grid:
      | Customer                  | First Name   | Last Name | Email Address              | Enabled | Confirmed |
      | Company A                 | CustomerUser | One       | user1@example.org          | Yes     | Yes       |
      | Company A                 | Amanda_upd   | Cole      | NewAmandaRCole@example.org | No      | Yes       |
      | Company A - West Division | Branda       | Sanborn   | BrandaJSanborn@example.org | Yes     | No        |
      | Company A - West Division | Ruth         | Maxwell   | RuthWMaxwell@example.org   | Yes     | Yes       |
      | Company A - West Division | NewFirst     | NewLast   | NewUser_UP@example.org     | Yes     | Yes       |
      | Company A                 | test         | test      | tester@example.org         | No      | Yes       |
    And I click view "NewAmandaRCole@example.org" in grid
    And I should see "Owner: John Doe"
    And I should see Customer User with:
      | Name Prefix | Amanda Pre       |
      | First Name  | Amanda_upd       |
      | Middle Name | AmandaMiddleName |
      | Name Suffix | Cole Suff        |
      | Birthday    | Oct 21, 1980     |
      | Website     | Default          |
