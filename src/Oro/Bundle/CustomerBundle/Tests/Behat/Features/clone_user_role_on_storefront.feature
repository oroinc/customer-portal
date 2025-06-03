@ticket-BB-25656
@fixture-OroCustomerBundle:CustomerUserAmandaFixture.yml

Feature: Clone user role on storefront
  In order to clone user permissions
  As an Customer
  I want to be able to clone predefined user roles with correct permissions

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Clone buyer role with permissions
    Given I operate as the Buyer
    And I login as AmandaRCole@example.org buyer
    When I click "Account Dropdown"
    And click "Roles"
    And I click Edit Buyer in grid
    When I fill form with:
      | Role Title | New1 Buyer Role |
    When I select customer user role permissions:
      | Agent Thread | Edit:None |
    Then I should see 'Predefined roles cannot be edited directly. We copied all the original data so that you can save it as a new user role for your organization. All users will be moved from the original role to this new role after you click "Save".' flash message and I close it
    When I click "Save"
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check buyer cloned role permissions on backend
    Given I proceed as the Admin
    And I login as administrator
    When I go to Customers/ Customer User Roles
    And click edit "New1 Buyer Role" in grid
    # Without fix new role permission to Agent Message copied not correct from Buyer role and set to None
    Then the customer user role has following active permissions:
      | Agent Message | View:Full                   | Create:Full                   | Edit:Full | Delete:Full                   |                               |
      | Agent Thread  | View:Corporate (All Levels) | Create:Corporate (All Levels) | Edit:None | Delete:Corporate (All Levels) | Assign:Corporate (All Levels) |

  Scenario: Clone buyer role with changed permissions
    Given I operate as the Buyer
    When I click "Account Dropdown"
    And click "Roles"
    Then I click Edit "New1 Buyer Role" in grid
    When I select customer user role permissions:
      | Agent Thread | Edit:Corporate (All Levels) |
    And I fill form with:
      | Role Title | New2 Buyer Role |
    When I click "Save"
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check buyer cloned role with changed permissions on backend
    Given I proceed as the Admin
    When I go to Customers/ Customer User Roles
    And click edit "New2 Buyer Role" in grid
    Then the customer user role has following active permissions:
      | Agent Message | View:Full                   | Create:Full                   | Edit:Full                   | Delete:Full                   |                               |
      | Agent Thread  | View:Corporate (All Levels) | Create:Corporate (All Levels) | Edit:Corporate (All Levels) | Delete:Corporate (All Levels) | Assign:Corporate (All Levels) |
