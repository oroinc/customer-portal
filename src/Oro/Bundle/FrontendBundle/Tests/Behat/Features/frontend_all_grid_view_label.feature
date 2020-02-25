@ticket-BAP-19446
@fixture-OroFrontendBundle:frontend_all_grid_view_label.yml

Feature: Frontend All Grid View Label
  In order to correctly translate store front application to different languages
  As an Administrator
  I want to be able to set translation for All grid view label

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Check All grid view label
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And follow "Account"
    When click "Users"
    Then I should see "All Users"

  Scenario: Change language settings
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    When I fill form with:
      | Enabled Localizations | [Zulu_Loc] |
      | Default Localization  | Zulu_Loc   |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Check All grid view label is changed
    Given I proceed as the Buyer
    And I reload the page
    Then I should see "All ZuluUsers"
