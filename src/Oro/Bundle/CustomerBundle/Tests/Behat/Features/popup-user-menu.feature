@fixture-BuyerCustomerFixture.yml

Feature: Popup user menu
  In order to provide optimized user experience based on the customer user menu configuration
  As an Administrator
  I want to choose the template for the customer user menu

  # Description
  # Based on the setting selected in system configuration use one of the two tempaltes to display customer user menu:
  # all menu items are displayed on the page
  # menu items are displayed in a drop-down when user clicks on the user name
  # Use different welcome messages in the templates:
  # default template (all at once) - "Signed in as: John Doe"
  # when only name is shown - "Welcome, John Doe"
  #
  # Configuration
  # Add new fieldset "Menu Templates" to the System -> Configuration -> COMMERCE -> Design -> Theme page:
  # new field "User Menu" - drop-down, default value - "Show all items at once", hint - none, values:
  # Show all items at once
  # Show subitems in a popup
  # These settings should be configurable on the global, organization and website levels.
  #
  # Acceptance Criteria
  # Show how an administrator can change the template used for customer user menu
  # Show how the "popup" template works

  Scenario: Site level - Allow User Configuration
    Given I login as administrator
    And go to System / Configuration
    And I click "Commerce"
    And I click "Design"
    And I click "Theme"
    And fill "Menu Templates Form" with:
      | Use Default  | false                    |
      | User Menu    | Show subitems in a popup |
    And save form
    And click "Save settings"
    And I click Logout in user menu
    Then I signed in as NancyJSallee@example.org on the store frontend
    And I should see an "Popup User Menu" element
