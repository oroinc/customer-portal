@regression
@ticket-BB-9559
@ticket-BAP-21510
Feature: Unable and dislable menu item with different User Agent and screens rules
  In order to selectively hide some menu items on smaller screens
  As an Adminstrator
  I want when managing frontend menus to specify on which screens size a specific menu item should or should not be shown

  Scenario: Create different window session
    Given sessions active:
      | Admin       | first_session  |
      | User        | second_session |
      | user_mobile | mobile_session |

  Scenario: Change menu item with User Agent Rules
    Given I proceed as the Admin
    And login as administrator
    And I go to System/Frontend Menus
    And click view "commerce_footer_links" in grid
    And I click Information in menu tree
    And I click "Create Menu Item"
    And I click "Add User Agent Condition"
    And I click "And User Agent Condition"
    And I click "Add User Agent Condition"
    And I click "Add User Agent Condition"
    When I fill "Commerce Menu Form" with:
      | Title                             | Screens Menu Item      |
      | Target Type                       | URI                    |
      | URI                               | http://www.example.com |
      | User Agent Contains Value         | containsTestAgent      |
      | Matches Operation                 | matches                |
      | User Agent Matches Value          | TestAgentChrome        |
      | Does Not Contain Operation        | does not contain       |
      | User Agent Does Not Contain Value | notContainTestAgent    |
      | Does Not Match Operation          | does not match         |
      | User Agent Does Not Match Value   | notContainTestAgent$   |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the User
    And I am on the homepage
    Then I should see "Screens Menu Item"

  Scenario: Check menu item visible with screen rule (tablet version)
    Given I proceed as the User
    And I set window size to 768x1024
    And I click on "Information"
    Then I should see "Screens Menu Item"

  Scenario: Check menu item visible with screen rule (mobile version)
    Given I proceed as the user_mobile
    And I am on homepage
    And I click on "Information"
    Then I should see "Screens Menu Item"

  Scenario: Change menu item with Screens Rules
    Given I proceed as the Admin
    When I fill "Commerce Menu Form" with:
      | Exclude On Screens | Laptops and desktops with 13 in. + screens |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the User
    And I am on the homepage
    Then I should not see "Screens Menu Item"

  Scenario: Check menu item unvisible with screen rule (tablet version)
    Given I proceed as the Admin
    When I fill "Commerce Menu Form" with:
      | Exclude On Screens | Tablet devices with up to 1600x1024 screen resolution. |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the User
    Given I set window size to 768x1024
    And I am on the homepage
    And I click on "Information"
    Then I should not see "Screens Menu Item"

  Scenario: Check menu item unvisible with screen rule (mobile version)
    Given I proceed as the Admin
    When I fill "Commerce Menu Form" with:
      | Exclude On Screens | Mobile optimized view. |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the user_mobile
    And I am on homepage
    And I click on "Information"
    Then I should not see "Screens Menu Item"

  Scenario: Hide footer menu items title
    Given I proceed as the Admin
    And I go to System/Frontend Menus
    And click view "commerce_footer_links" in grid
    And I click Information in menu tree
    And I click "Hide"
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the User
    And I am on the homepage
    Then I should not see "Information"

  Scenario: Show footer menu items title
    Given I proceed as the Admin
    And I go to System/Frontend Menus
    And click view "commerce_footer_links" in grid
    And I click Information in menu tree
    And I click "Show"
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the User
    And I am on the homepage
    Then I should see "Information"

  Scenario: Create menu item with passing User Agent Rules
    Given I proceed as the Admin
    When I go to System/Frontend Menus
    And click view "commerce_footer_links" in grid
    And I click Information in menu tree
    And I click "Create Menu Item"
    And I click "Add User Agent Condition"
    And I fill "Commerce Menu Form" with:
      | Title                      | Test User Agent        |
      | Target Type                | URI                    |
      | URI                        | http://www.example.com |
      | User Agent First Operation | contains               |
      | User Agent First Value     | Chrome                 |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the User
    And I am on the homepage
    Then I should see "Test User Agent"

  Scenario: Create menu item with non-passing User Agent Rules
    Given I proceed as the Admin
    When I fill "Commerce Menu Form" with:
      | User Agent First Operation | does not contain |
      | User Agent First Value     | Chrome           |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I proceed as the User
    And I am on the homepage
    Then I should not see "Test User Agent"
