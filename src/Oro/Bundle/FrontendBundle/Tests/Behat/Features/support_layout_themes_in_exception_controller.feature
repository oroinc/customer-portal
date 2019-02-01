@ticket-BB-16117
Feature: Support layout themes in exception controller
  In order to be able to use third-party themes on page error
  As an administrator
  I changing the theme in system configuration

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Check exception page on default theme
    Given I proceed as the User
    When I am on "/non_exist_page_#1"
    Then Page title equals to "Not Found"

  Scenario: Change layout theme to blank
    Given I proceed as the Admin
    And I login as administrator
    And go to System/ Configuration
    And I follow "Commerce/Design/Theme" on configuration sidebar
    And fill "Theme Templates Form" with:
      | Use Default | false |
      | Theme       | Blank |
    When save form
    Then I should see "Configuration saved" flash message

  Scenario: Check exception page on blank theme
    Given I proceed as the User
    When I reload the page
    Then Page title equals to "%status%"
