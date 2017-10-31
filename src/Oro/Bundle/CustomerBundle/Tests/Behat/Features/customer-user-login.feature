@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Customer login

  Scenario: Check successful login and logout of buyer
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I should see text matching "Signed in as: Amanda Cole"
    Then click "Sign Out"
    And I should not see text matching "Signed in as: Amanda Cole"

  Scenario: Proper email validation message
    Given I login as usernameNotEmail buyer
    And I should see validation errors:
      | Email Address | This value is not a valid email address. |
