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
    When I follow "Account"
    And click "Roles"
    And click edit "Buyer" in grid
    Then I fill form with:
      | Role Title | New1 Buyer Role |
    And I select customer user role permissions:
      | Checkout | Edit:None |
    When I click "Save"
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check buyer cloned role permissions on backend
    Given I proceed as the Admin
    And I login as administrator
    When I go to Customers/ Customer User Roles
    And click edit "New1 Buyer Role" in grid
    Then the customer user role has following active permissions:
      | Checkout | View:User (Own) | Create:User (Own) | Edit:None | Delete:User (Own) | Assign:None |

  Scenario: Clone buyer role with changed permissions
    Given I operate as the Buyer
    When I follow "Account"
    And click "Roles"
    Then I click edit "New1 Buyer Role" in grid
    When I select customer user role permissions:
      | Checkout | Edit:User (Own) |
    And I fill form with:
      | Role Title | New2 Buyer Role |
    When I click "Save"
    Then I should see "Customer User Role has been saved" flash message

  Scenario: Check buyer cloned role with changed permissions on backend
    Given I proceed as the Admin
    When I go to Customers/ Customer User Roles
    And click edit "New2 Buyer Role" in grid
    Then the customer user role has following active permissions:
      | Checkout | View:User (Own) | Create:User (Own) | Edit:User (Own) | Delete:User (Own) | Assign:None |
