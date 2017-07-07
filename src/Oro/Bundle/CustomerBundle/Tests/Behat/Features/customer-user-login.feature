Feature: Customer login

  Scenario: Proper email validation message
    Given I login as usernameNotEmail buyer
    And I should see validation errors:
      | Email Address | This value is not a valid email address. |
