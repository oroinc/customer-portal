@fixture-OroCustomerBundle:BuyerCustomerWithChildFixture.yml

Feature: Update Customer User Roles List
  In order to control data available to customer user
  As a customer user
  I should see updated data when the list of roles updated for me

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Check customer users list with default roles list
    Given I operate as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Users"
    Then I should see following records in grid:
      | Amanda |
      | Nancy  |
    And I should not see "Ruth"

  Scenario: Add administrator role to customer user
    Given I operate as the Admin
    And I login as administrator
    And I go to Customers/Customer Users
    And I click edit AmandaRCole@example.org in grid
    And I fill form with:
      | Roles | Administrator   |
    When I save and close form
    Then I should see "Customer User has been saved" flash message

  Scenario: Check customer users list with updated roles list
    Given I operate as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Users"
    Then I should see following records in grid:
      | Amanda |
      | Nancy  |
      | Ruth   |
