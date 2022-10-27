@regression
@ticket-BB-20478
@fixture-OroUserBundle:UserLocalizations.yml
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Localized email notification for customer user on storefront
  In order to receive emails
  As a customer user
  I need to receive emails in current language

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Prepare localization configuration
    Given I proceed as the Admin
    And login as administrator
    And go to System / Websites
    And click Configuration "Default" in grid
    When I follow "System Configuration/General Setup/Localization" on configuration sidebar
    And uncheck "Use Organization" for "Default Localization" field
    And fill form with:
      | Enabled Localizations | [English (United States), German Localization] |
      | Default Localization  | English (United States)                        |
    And submit form
    Then I should see "Configuration saved" flash message

  Scenario: Prepare email template with different localizations
    Given I go to System / Emails / Templates
    When I filter Template Name as is equal to "customer_user_welcome_email_registered_by_admin"
    And click "edit" on first row in grid
    And fill "Email Template Form" with:
      | Subject | English Customer User Welcome By Admin Subject |
      | Content | English Customer User Welcome By Admin Body    |
    And click "German"
    And fill "Email Template Form" with:
      | Subject Fallback | false                                         |
      | Content Fallback | false                                         |
      | Subject          | German Customer User Welcome By Admin Subject |
      | Content          | German Customer User Welcome By Admin Body    |
    And submit form
    Then I should see "Template saved" flash message

  Scenario: Customer user authorization
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend

  Scenario Outline: Check email send to the customer user when register by storefront administrator by different localizations
    Given I am on the homepage
    And click "Localization Switcher"
    And select "<Locale>" localization
    And follow "Account"
    And click "Users"
    And click "Create User"
    When I fill form with:
      | Email Address      | <Email> |
      | First Name         | Branda  |
      | Last Name          | Cole    |
      | Password           | <Email> |
      | Confirm Password   | <Email> |
      | Buyer (Predefined) | true    |
      | Send Welcome Email | true    |
    And click "Save"
    Then I should see "Customer User has been saved" flash message
    And email with Subject "<Subject>" containing the following was sent:
      | Body | <Body> |
    Examples:
      | Email                | Subject                                        | Body                                        | Locale                  |
      | Example1@example.com | German Customer User Welcome By Admin Subject  | German Customer User Welcome By Admin Body  | German Localization     |
      | Example2@example.com | English Customer User Welcome By Admin Subject | English Customer User Welcome By Admin Body | English (United States) |
