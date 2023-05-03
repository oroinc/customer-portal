@ticket-BB-20506
@ticket-BB-14870

Feature: Import Button Should Be Invisible When Permission Set to None
  In order to prevent to use import function from user who doesn't have correct permission
  As an Administrator
  I should not see the import button when I don't have the permission to certain entity.

  Scenario: Set entity Customer's permission under Role Administrator to not able to edit
    Given I login as administrator
    And I go to System/ User Management/ Roles
    And I click edit "Administrator" in grid
    And I select following permissions:
      | Customer | Edit:None |
    Then I save form
    And I should see "Role saved" flash message

  Scenario: Import to create customer with permission
    Given I go to Customers/ Customers
    And I download "Customers" Data Template file
    And I fill template with data:
      | Id | Name           | Parent Id | Parent Parent Id | Parent Owner Id | Group Name    | Owner Username | Owner Id | Tax code   | Account Id | VAT Id | Internal rating Id | Payment term Label |
      |    | Dummy Customer |           |                  |                 |               |                | 1        |            |            |        |                    |                    |
    When I import file
    Then Email should contains the following "Errors: 0 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text
    And Email should not contains the following:
      | Body | Error Log |
    When I reload the page
    Then I should see following grid:
      | NAME           | GROUP         | PARENT CUSTOMER | INTERNAL RATING | PAYMENT TERM | TAX CODE   | ACCOUNT        |
      | Dummy Customer |               |                 |                 |              |            | Dummy Customer |
    And number of records should be 1

  Scenario: Import to replace customer without permission
    Given I go to Customers/ Customers
    And I download "Customers" Data Template file
    And I fill template with data:
      | Id | Name                   | Parent Id | Parent Parent Id | Parent Owner Id | Group Name    | Owner Username | Owner Id | Tax code   | Account Id | VAT Id | Internal rating Id | Payment term Label |
      | 1  | Another Dummy Customer |           |                  |                 |               |                | 1        |            |            |        |                    |                    |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 0, read: 1, added: 0, updated: 0, replaced: 0" text
    And Email should contains the following:
      | Body | Error Log |
    When I reload the page
    Then I should see following grid:
      | NAME           | GROUP         | PARENT CUSTOMER | INTERNAL RATING | PAYMENT TERM | TAX CODE   | ACCOUNT        |
      | Dummy Customer |               |                 |                 |              |            | Dummy Customer |
    And number of records should be 1

  Scenario: Set entity Customer's permission under Role Administrator to not able to create
    Given I go to System/ User Management/ Roles
    And I click edit "Administrator" in grid
    And I select following permissions:
      | Customer | Create:None | Edit:Global |
    Then I save form
    And I should see "Role saved" flash message

  Scenario: Import to replace and create customer both to test permission
    Given I go to Customers/Customers
    And I download "Customers" Data Template file
    And I fill template with data:
      | Id | Name                   | Parent Id | Parent Parent Id | Parent Owner Id | Group Name    | Owner Username | Owner Id | Tax code   | Account Id | VAT Id | Internal rating Id | Payment term Label |
      | 1  | Another Dummy Customer |           |                  |                 |               |                | 1        |            |            |        |                    |                    |
      |    | The 3rd Dummy Customer |           |                  |                 |               |                | 1        |            |            |        |                    |                    |
    When I import file
    Then Email should contains the following "Errors: 1 processed: 1, read: 2, added: 0, updated: 0, replaced: 1" text
    And Email should contains the following:
      | Body | Error Log |
    When I reload the page
    Then I should see following grid:
      | NAME                   | GROUP         | PARENT CUSTOMER | INTERNAL RATING | PAYMENT TERM | TAX CODE   | ACCOUNT                |
      | Another Dummy Customer |               |                 |                 |              |            | Dummy Customer |
    And number of records should be 1

  Scenario: Set entity Customer's permission under Role Administrator to not able to both create and edit
    Given I go to System/ User Management/ Roles
    And I click edit "Administrator" in grid
    And I select following permissions:
      | Customer | Create:None | Edit:None |
    And I save form
    Then I should see "Role saved" flash message
    When I go to Customers/Customers
    Then I should see "Import file"
    When I click "Import file"
    Then I should see a "Customer Addresses Tab" element
    And I should see "Download Import Template"
    And I should not see a "Customer Tab" element
    And I close ui dialog

  Scenario: Set entity Customer Address's  permission under Role Administrator to not able to both create and edit
    Given I go to System/ User Management/ Roles
    And I click edit "Administrator" in grid
    And I select following permissions:
      | Customer Address | Create:None | Edit:None |
    And I save form
    Then I should see "Role saved" flash message
    When I go to Customers/Customers
    Then I should not see "Import file"
