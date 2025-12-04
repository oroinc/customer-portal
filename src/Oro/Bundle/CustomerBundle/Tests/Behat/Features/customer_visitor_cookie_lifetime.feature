@regression
@ticket-BB-10691
@fixture-OroShoppingListBundle:ProductFixture.yml
Feature: Customer visitor cookie lifetime
  As an administrator
  I want to determine how many days customer visitor (anonymous user) session will be active till reset

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Enable guest shopping list and update customer visitor cookie lifetime setting
    Given I proceed as the Admin
    And I login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Sales/Shopping List" on configuration sidebar
    And uncheck "Use default" for "Enable Guest Shopping List" field
    And I check "Enable Guest Shopping List"
    And I save form
    Then I should see "Configuration saved" flash message
    When I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Customer visitor cookie lifetime (days)" field
    And I fill form with:
      | Customer visitor cookie lifetime (days) | 1 |
    And uncheck "Use default" for "Create Customer Visitors Immediately" field
    And I uncheck "Create Customer Visitors Immediately"
    And I save form
    Then I should see "Configuration saved" flash message

  Scenario: Add product to shopping list and check customer visitor cookie
    Given I proceed as the User
    When I am on the homepage
    Then Customer visitor cookie should not exist
    When I type "SKU003" in "search"
    And I click "Search Button"
    And I click "Add to Shopping List" for "SKU003" product
    Then Customer visitor cookie expiration date should be "+1 day"

  Scenario: Validate wrong data
    Given I proceed as the Admin
    When I fill form with:
      | Customer visitor cookie lifetime (days) | -1 |
    Then I should see validation errors:
      | Customer visitor cookie lifetime (days) | This value should be 1 or more. |
    When I fill form with:
      | Customer visitor cookie lifetime (days) | Lorem ipsum |
    Then I should see validation errors:
      | Customer visitor cookie lifetime (days) | This value should be a valid number. |
