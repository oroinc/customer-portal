@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Access user profile
  ToDo: BAP-16103 Add missing descriptions to the Behat features

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Redirect to login when not logged-in user try to access user profile page
    Given I proceed as the Buyer
    And I am on "customer/profile"
    And Page title equals to "Sign In"
    When I signed in as NancyJSallee@example.org on the store frontend
    And I should see an "Account link" element
    And I click "Account"
    Then I should see "My Profile"

  Scenario: Customer user role change
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers/Customer User Roles
    And I click edit Administrator in grid
    When select following permissions:
      | Customer User | View:None |
    And I save and close form
    Then I should see "Customer User Role has been saved"

  Scenario: Customer user role change
    Given I proceed as the Buyer
    And I am on the homepage
    When I reload the page
    Then I should not see an "Account link" element
