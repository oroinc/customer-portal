@ticket-BB-10691
Feature: Customer visitor cookie lifetime
  As an administrator
  I want to determine how many days customer visitor (anonymous user) session will be active till reset

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Update customer visitor cookie lifetime setting
    Given I proceed as the Admin
    And I login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Customer/Customer Users" on configuration sidebar
    And uncheck "Use default" for "Customer visitor cookie lifetime (days)" field
    When I fill form with:
      | Customer visitor cookie lifetime (days) | 1 |
    And I save form
    Then I should see "Configuration saved" flash message

  Scenario: Update customer visitor cookie lifetime setting
    Given I proceed as the User
    When I am on the homepage
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

