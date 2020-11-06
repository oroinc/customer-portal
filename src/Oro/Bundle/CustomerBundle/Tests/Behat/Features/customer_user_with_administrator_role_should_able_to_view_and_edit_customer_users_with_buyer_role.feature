Feature: Customer User with administrator role should able to view and edit Customer Users with buyer role
  In order to check ACL for Customer Users
  As an User
  I want to be sure that Customer User with administrator role should able to view and edit Customer Users with buyer role

  Scenario: Create different window session
    Given sessions active:
      | User  | first_session  |
      | Admin | second_session |

  Scenario: Create new user and edit him
    Given I proceed as the User
    And I am on the homepage
    And click "Sign In"
    And click "Create An Account"
    And fill form with:
      | Company Name     | TestCompany        |
      | First Name       | Test_O             |
      | Last Name        | Test_M             |
      | Email Address    | Testuser1@test.com |
      | Password         | Testuser1@test.com |
      | Confirm Password | Testuser1@test.com |
    And click "Create An Account"
    And I proceed as the Admin
    And login as administrator
    And go to Customers/Customer Users
    And click view "Testuser1@test.com" in grid
    And click "Confirm"
    And I should see "Confirmation successful" flash message
    And I proceed as the User
    And I signed in as Testuser1@test.com on the store frontend
    And I proceed as the Admin
    And go to Customers/Customer Users
    And click edit "Testuser1@test.com" in grid
    And fill "Customer User Form Admin" with:
      | Administrator Role | true  |
      | Buyer Role         | false |
    And save and close form
    And should see "Customer User has been saved" flash message
    And I proceed as the User
    And click "Sign Out"
    And I signed in as Testuser1@test.com on the store frontend
    And follow "Account"
    And click "Users"
    And click "Create User"
    And fill form with:
      | Email Address      | newuser@test.com |
      | First Name         | newFirst         |
      | Last Name          | newLast          |
      | Password           | 25253124Ff       |
      | Confirm Password   | 25253124Ff       |
      | Buyer (Predefined) | true             |
    And click "Save"
    And should see "Customer User has been saved" flash message
    And click "Users"
    When click view "newuser@test.com" in grid
    Then should see "CUSTOMER USER - newFirst newLast"

  Scenario: View new user
    Given I proceed as the User
    And click "Users"
    When click view "newuser@test.com" in grid
    Then should see "CUSTOMER USER - newFirst newLast"

  Scenario: Edit new user
    Given I proceed as the User
    And click "Users"
    When click edit "newuser@test.com" in grid
    Then should see "EDIT CUSTOMER USER - newFirst newLast"
