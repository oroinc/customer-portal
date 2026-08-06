@regression
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Storefront back and cancel buttons navigation
  In order to continue working from the place I came from
  As a Customer User
  I need the Back and Cancel buttons to lead to the previously visited page

  Scenario: Back button walks back through the edit and view pages to the users grid
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Users"
    When I click view "NancyJSallee@example.org" in grid
    Then the url should match "/customer/user/view"
    When I click "Edit"
    Then the url should match "/customer/user/update"
    When I click "Back"
    Then the url should match "/customer/user/view"
    And I should see "CUSTOMER USER - Nancy Sallee"
    When I click "Back"
    Then I should see following records in grid:
      | Amanda |
      | Nancy  |

  Scenario: Cancel button on the customer user edit page returns to the previously visited view page
    When I click view "NancyJSallee@example.org" in grid
    Then the url should match "/customer/user/view"
    When I click "Edit"
    Then the url should match "/customer/user/update"
    When I click "Cancel"
    Then the url should match "/customer/user/view"
    And I should see "CUSTOMER USER - Nancy Sallee"

  Scenario: Back and Cancel buttons on the role edit page return to the previously visited role view page
    Given I click "Account Dropdown"
    And I click "Roles"
    When I click view "Buyer" in grid
    Then the url should match "/customer/roles/view"
    When I click "Edit Role"
    Then the url should match "/customer/roles/update"
    When I click "Back"
    Then the url should match "/customer/roles/view"
    When I click "Edit Role"
    Then the url should match "/customer/roles/update"
    When I click on "Customer User Role Cancel Button"
    Then the url should match "/customer/roles/view"
