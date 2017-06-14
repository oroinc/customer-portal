@fixture-BB-10191.yml
Feature: BB-10191
  Scenario: Create new user and edit him
    Given I signed in as AmandaRCole@example.org on the store frontend
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
    Given click "Users"
    When click edit "newuser@test.com" in grid
    Then should see "EDIT CUSTOMER USER - newFirst newLast"
    And click "Sign Out"
