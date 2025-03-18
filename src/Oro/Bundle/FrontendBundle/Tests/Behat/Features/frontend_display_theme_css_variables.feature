@feature-BB-23878
Feature: Frontend Display Theme Css Variables
  In order to see css variables on store front application as an Guest
  As an Administrator
  I want to fill up current theme with few css variables

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Guest | second_session |
    And I proceed as the Admin
    And I login as administrator
    And I go to System / Theme Configurations
    And I click Edit "Refreshing Teal" in grid

  Scenario Outline: Check inline validation for the all css types
    When I fill "Theme Configuration Form" with:
      | <Form Element> | %2 |
    Then I should see "This value should be valid <Css Type>"
    Examples:
      | Form Element                      | Css Type          |
      | Default Font Size                 | CSS font size     |
      | Default Line Height               | CSS line height   |
      | Footer Background                 | CSS background    |
      | Btn Min Height                    | CSS min height    |
      | Btn Border Width                  | CSS length        |
      | Btn Padding                       | CSS padding       |
      | Btn Gap                           | CSS gap           |
      | Btn Border Radius                 | CSS border radius |
      | Btn Neutral Dark Focus Box Shadow | CSS box shadow    |
      | Btn Neutral Dark Focus Outline    | CSS outline       |

  Scenario: Fill up form with valid css values
    When I fill "Theme Configuration Form" with:
      | Default Font Size                 | 1em                      |
      | Primary Hover                     | #000000                  |
      | Default Line Height               | 16px                     |
      | Footer Background                 | grey                     |
      | Btn Min Height                    | 36px                     |
      | Btn Border Width                  | 3px                      |
      | Btn Padding                       | 10px 2px 8px             |
      | Btn Gap                           | 2px                      |
      | Btn Border Radius                 | 25%                      |
      | Btn Neutral Dark Focus Box Shadow | inset 10px 5px 5px black |
      | Btn Neutral Dark Focus Outline    | 3px                      |
    And I set color 'Cornflower Blue' for Primary Main Color
    And I save and close form
    Then I should see "Theme Configuration has been saved" flash message

  Scenario: Check that css variables are displayed on store front application
    Given I proceed as the Guest
    When I am on homepage
    Then I should see these theme css variables:
      | variable                          | value                    |
      | primary-hover                     | #000000                  |
      | primary-main                      | #6D8DD4                  |
      | base-font-size                    | 1em                      |
      | base-line-height                  | 16px                     |
      | footer-background                 | grey                     |
      | btn-min-height                    | 36px                     |
      | btn-padding                       | 10px 2px 8px             |
      | btn-gap                           | 2px                      |
      | btn-border-width                  | 3px                      |
      | btn-border-radius                 | 25%                      |
      | btn-neutral-dark-focus-outline    | 3px                      |
      | btn-neutral-dark-focus-box-shadow | inset 10px 5px 5px black |
