Feature: BB-10191
  Scenario: Create different window session
    Given sessions active:
      | User  |first_session |
      | Admin |second_session|

  Scenario: Create new user and edit him
    Given I proceed as the User
    And I am on the homepage
    And click "Sign In"
    And click "Create An Account"
    And fill form with:
    |Company Name    |TestCompany       |
    |First Name      |Test_O            |
    |Last Name       |Test_M            |
    |Email Address   |Testuser1@test.com|
    |Password        |Testuser1@test.com|
    |Confirm Password|Testuser1@test.com|
    And click "Create An Account"
    And I proceed as the Admin
    And login as administrator
    And go to Customers/Customer Users
    And click view "Testuser1@test.com" in grid
    And click "Confirm"
    And I should see "Confirmation successful" flash message
    And go to Customers/Customer Users
    And click "Create Customer User"
    And fill "Customer User Form Admin" with:
    |First Name        |FirstN            |
    |Last Name         |LastN             |
    |Customer          |TestCompany       |
    |Password          |Testuser2@test.com|
    |Confirm Password  |Testuser2@test.com|
    |Administrator Role|true              |
    |Email Address     |Testuser2@test.com|
    And click "Today"
    And save and close form
    And should see "Customer User has been saved" flash message
    And I proceed as the User
    And I signed in as Testuser2@test.com on the store frontend
    And click "Account"
    And click "Users"
    And click "Create User"
    And fill "Customer User Form" with:
    |Email Address     |newuser@test.com|
    |First Name        |newFirst        |
    |Last Name         |newLast         |
    |Password          |25253124Ff      |
    |Confirm Password  |25253124Ff      |
    |Buyer (Predefined)|true            |
    And click "Save"
    And should see "Customer User has been saved" flash message
    And click "Users"
    When click view "newuser@test.com" in grid
    Then should see "CUSTOMER USER - newFirst newLast"

  Scenario: Edit new user
    Given I proceed as the User
    And click "Users"
    When click edit "newuser@test.com" in grid
    Then should see "EDIT CUSTOMER USER - newFirst newLast"
    And click "Sign Out"
