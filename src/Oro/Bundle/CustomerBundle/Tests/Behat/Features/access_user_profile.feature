@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Access user profile
  ToDo: BAP-16103 Add missing descriptions to the Behat features

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Signed in user is able to visit his profile page
    Given I proceed as the Buyer
    And I am on "customer/profile"
    And Page title equals to "Sign In"
    When I signed in as NancyJSallee@example.org on the store frontend
    And I should see an "Account link" element
    And I click "Account"
    Then I should see "My Profile"

  Scenario: Set Customer User View permission to None
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers/Customer User Roles
    And I click edit Administrator in grid
    When select following permissions:
      | Customer User | View:None |
    And I save and close form
    Then I should see "Customer User Role has been saved"

  Scenario: Account menu is shown when Update User Profile permission is checked and Customer User View is None
    Given I proceed as the Buyer
    And I am on the homepage
    When I reload the page
    Then I should see an "Account link" element

  Scenario: Uncheck Update User Profile and set Customer User View to Corporate
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    And I click edit Administrator in grid
    When select following permissions:
      | Customer User | View:Сorporate (All Levels) |
    And I uncheck "Update User Profile" entity permission
    And I save and close form
    Then I should see "Customer User Role has been saved"

  Scenario: Account menu is shown when Update User Profile permission is unchecked and Customer User View is Corporate
    Given I proceed as the Buyer
    And I am on the homepage
    When I reload the page
    Then I should see an "Account link" element

  Scenario: Set Customer User View to None
    Given I proceed as the Admin
    And I go to Customers/Customer User Roles
    And I click edit Administrator in grid
    When select following permissions:
      | Customer User | View:None |
    And I save and close form
    Then I should see "Customer User Role has been saved"

  Scenario: Account menu is hidden when Update User Profile permission is unchecked and Customer User View is None
    Given I proceed as the Buyer
    And I am on the homepage
    When I reload the page
    Then I should not see an "Account link" element
