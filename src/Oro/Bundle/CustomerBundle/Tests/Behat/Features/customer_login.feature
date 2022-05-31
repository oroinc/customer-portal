@fixture-OroCustomerBundle:CustomerUserFixture.yml
@fixture-OroLocaleBundle:ZuluLocalization.yml

Feature: Customer login
  In order to login user on front store
  As a Buyer
  I want to be able to login on front store, logout and have detailed error message when there was login attempt with bad credentials

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |
    And I enable the existing localizations

  Scenario: Check redirect to login page
    Given I proceed as the User
    When I go to "/customer/user/login-check"
    Then I should be on Customer User Login page

  Scenario: Check unsuccessful login error
    Given I am on the homepage
    And I click "Sign In"
    And I fill form with:
      | Email Address | NotExistingAddress@example.com |
      | Password      | test                           |
    When I click "Sign In"
    Then I should see "Your login was unsuccessful. Please check your e-mail address and password before trying again. If you have forgotten your password, follow \"Forgot Your password?\" link."

  Scenario: Check successful login and logout of buyer
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I should see text matching "Signed in as: Amanda Cole"
    Then click "Sign Out"
    And I should not see text matching "Signed in as: Amanda Cole"

  Scenario: Check redirect to profile
    Given I signed in as AmandaRCole@example.org on the store frontend
    When I go to "/customer/user/login-check"
    Then I should be on Customer User Profile page

  Scenario: Proper email validation message
    Given I login as usernameNotEmail buyer
    And I should see validation errors:
      | Email Address | This value is not a valid email address. |

  Scenario: Add translation for unsuccessful login error on frontstore
    Given I proceed as the Admin
    And I login as administrator
    And go to System/Localization/Translations
    When filter Translated Value as is empty
    And filter English translation as contains "Your login was unsuccessful"
    Then I edit "oro_customer.login.errors.bad_credentials" Translated Value as "Your login was unsuccessful - Zulu"
    And I should see following records in grid:
      |Your login was unsuccessful - Zulu|

  Scenario: Check translated unsuccessful login error
    Given I proceed as the User
    And I am on the homepage
    And I click "Localization Switcher"
    And I select "Zulu" localization
    And I click "Sign In"
    And I fill form with:
      | Email Address | NotExistingAddress@example.com |
      | Password      | test                           |
    When I click "Sign In"
    Then I should see "Your login was unsuccessful - Zulu"

  Scenario: Check redirect to login page after remove session
    Given I signed in as AmandaRCole@example.org on the store frontend
    When I go to "/customer/user/login-check"
    Then I should be on Customer User Profile page
    When I restart the browser
    Then I should see "Sign In"
    And I should not see "Signed in as: Amanda Cole"
