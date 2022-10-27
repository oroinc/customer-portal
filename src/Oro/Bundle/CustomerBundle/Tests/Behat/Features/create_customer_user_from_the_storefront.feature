@ticket-BB-15477
@ticket-BB-16456
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroCustomerBundle:CustomerUserRoleFixture.yml

Feature: Create customer user from the store-front
  In order to manage customer users
  As an administrator
  I want to create new Customer user

  Scenario: Create new user with invalid email from front-store
    Given I signed in as NancyJSallee@example.org on the store frontend
    And follow "Account"
    And click "Users"
    And click "Create User"
    And fill form with:
      | Email Address      | just"not"right@example.com |
      | First Name         | newFirst                   |
      | Last Name          | newLast                    |
      | Password           | 25253124Ff                 |
      | Confirm Password   | 25253124Ff                 |
      | Buyer (Predefined) | true                       |
    And click "Save"
    Then I should see validation errors:
      | Email Address | This value is not a valid email address. |
    And should not see "Customer User has been saved" flash message

  Scenario: Create new user from front-store
    Given fill form with:
      | Password         | 25253124Ff       |
      | Confirm Password | 25253124Ff       |
      | Email Address    | newuser@test.com |
    And click "Save"
    And should see "Customer User has been saved" flash message
    And click "Users"
    When click view "newuser@test.com" in grid
    Then should see "CUSTOMER USER - newFirst newLast"

  Scenario: Create new user from front-store with generated password
    Given I click "Users"
    And click "Create User"
    And fill form with:
      | Email Address      | newuser2@test.com |
      | First Name         | newFirst2         |
      | Last Name          | newLast2          |
      | Generate Password  | true              |
      | Buyer (Predefined) | true              |
    And click "Save"
    And should see "Customer User has been saved" flash message
    And click "Users"
    When click view "newuser2@test.com" in grid
    Then should see "CUSTOMER USER - newFirst2 newLast2"

  Scenario: Customer user should not see not self-managed roles in roles list during customer editing
    Given I click "Users"
    And I click "Create User"
    Then I should see "customer assigned self managed"
    And I should see "customer not assigned self managed"
    And I should not see "customer assigned not self managed"
    And I should not see "not customer not assigned not self managed"

  Scenario: Check created customer user at admin area
    Given I login as administrator
    And I go to Customers / Customer Users
    And I click View "newuser@test.com" in grid
    And I should see "Website Default"
