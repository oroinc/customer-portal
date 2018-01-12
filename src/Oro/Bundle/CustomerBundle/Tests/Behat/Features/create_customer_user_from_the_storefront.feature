@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Create customer user from the store-front
  In order to manage customer users
  As an administrator
  I want to create new Customer user

  Scenario: Create new user from front-store
    Given I signed in as NancyJSallee@example.org on the store frontend
    And click "Account"
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
