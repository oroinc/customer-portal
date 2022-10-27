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
