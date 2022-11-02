@fix-BB-16089
@fixture-OroCustomerBundle:CustomerFixture.yml
Feature: New admin role with global permissions can create customer users
  In order to create Customer Users
  As an Administrator
  I want to be able to create customer users when my role has such permissions

  Scenario: Create a Role with the global rights for Customer User entity and rights to view websites/customers/customer user roles
    Given I login as administrator
    And go to System/User Management/Roles
    And click "Create Role"
    And fill form with:
      | Role | Create Customer User Role |
    And select following permissions:
      | Customer User | View:Global | Create:Global | Edit:Global | Delete:Global | Assign:Global | Share:Global |
    And select following permissions:
      | Website            | View:Global |
      | Customer           | View:Global |
      | Customer User Role | View:Global |
    When save and close form
    Then I should see "Role saved" flash message

  Scenario: Create new User and assign only Create Customer User Role
    Given go to System/User Management/Users
    And click "Create User"
    And fill "User Form" with:
      | Username                  | User with Create Customer User Role |
      | Password                  | Administrator1@example.org          |
      | Re-Enter Password         | Administrator1@example.org          |
      | First Name                | First Name                          |
      | Last Name                 | Last Name                           |
      | Primary Email             | Administrator1@example.org          |
      | OroCRM Organization       | true                                |
      | Create Customer User Role | true                                |
      | Enabled                   | Enabled                             |
    When I save and close form
    Then I should see "User saved" flash message

  Scenario: Create Customer User as User with Create Customer User Role
    Given I login as "Administrator1@example.org" user
    And go to Customers/Customer Users
    And I click "Create Customer User"
    And fill form with:
      | First Name    | New                       |
      | Last Name     | Customer User             |
      | Email Address | CustomerUser1@example.org |
    And I focus on "Birthday" field
    And click "Today"
    And fill form with:
      | Password           | CustomerUser1@example.org |
      | Confirm Password   | CustomerUser1@example.org |
      | Customer           | WithCustomerUser          |
      | Buyer (Predefined) | true                      |
    When I save and close form
    Then I should see "Customer User has been saved" flash message
