@ticket-BB-20556
@fixture-OroCustomerBundle:CustomerFixture.yml
@regression

Feature: Create customer user
  In order to manage customer users
  As an administrator
  I want to create new Customer user

  Scenario: Create Customer User with invalid email
    Given I login as administrator
    And go to Customers/Customer Users
    And click "Create Customer User"
    When I fill form with:
      | First Name    | New                        |
      | Last Name     | Customer User              |
      | Email Address | just"not"right@example.com |
    And focus on "Birthday" field
    And click "Today"
    And fill form with:
      | Password           | CustomerUser1@example.org |
      | Confirm Password   | CustomerUser1@example.org |
      | Buyer (Predefined) | true                      |
      | Customer           | WithCustomerUser          |
    And the "Buyer (Predefined)" checkbox should be checked
    And save form
    Then I should see validation errors:
      | Email Address | This value is not a valid email address. |
    And should not see "Customer User has been saved" flash message

  Scenario: Create Customer User with already existing email address
    When I fill form with:
      | Email Address     | test1e@example.org |
      | Password          | Test1e@example.org |
      | Confirm Password  | Test1e@example.org |
    And save form
    Then I should see validation errors:
      | Email Address | This email is already used. |
    And the "Buyer (Predefined)" checkbox should be checked

  Scenario: Create Customer User
    When I fill form with:
      | Email Address      | CustomerUser1@example.org |
      | Password           | CustomerUser1@example.org |
      | Confirm Password   | CustomerUser1@example.org |
      | Buyer (Predefined) | true                      |
    And save form
    And should see "Customer User has been saved" flash message
    Then I should not see validation errors:
      | Email Address | This value is not a valid email address. |
    And the "Buyer (Predefined)" checkbox should be checked
