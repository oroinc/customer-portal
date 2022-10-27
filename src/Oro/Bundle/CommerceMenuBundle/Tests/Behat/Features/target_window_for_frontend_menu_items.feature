@ticket-BB-19112
@fixture-OroCMSBundle:CustomerUserFixture.yml
@fixture-OroCMSBundle:WysiwygRoleFixture.yml
@regression

Feature: Target Window for Frontend Menu Items
  In order to improve possibilities of the navigation menu management
  As an Administrator
  I need to add an option of opening navigation items on the Storefront

  Scenario: Add new menu item to Commerce Main Menu
    Given I login as administrator
    And I go to System/ Frontend Menus
    And I click view "commerce_main_menu" in grid
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | New Main About  |
      | Target Type | URI             |
      | URI         | /new-main-about |
    When I save form
    Then I should see "Menu item saved successfully" flash message
    And I should see that option "Same Window" is selected in "Target Window" select
    When I fill "Commerce Menu Form" with:
      | Target Window | New Window |
    And I save form
    Then I should see "Menu item saved successfully" flash message
    And I should see that option "New Window" is selected in "Target Window" select

  Scenario: Add new menu item to Commerce Footer Links
    Given I go to System/ Frontend Menus
    And I click view "commerce_footer_links" in grid
    And I click "Information"
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | New Footer About   |
      | Target Type | URI                |
      | URI         | /new-footer-about |
    When I save form
    Then I should see "Menu item saved successfully" flash message
    And I should see that option "Same Window" is selected in "Target Window" select
    When I fill "Commerce Menu Form" with:
      | Target Window | New Window |
    And I save form
    Then I should see "Menu item saved successfully" flash message
    And I should see that option "New Window" is selected in "Target Window" select

  Scenario: Check that Frontend Menu Items opens as configured
    Given I go to the homepage
    And I should see "New Main About"
    When I click "New Main About"
    Then a new browser tab is opened and I switch to it
    And I should see "New Footer About"
    When I click "New Footer About"
    Then a new browser tab is opened and I switch to it
    And I should see "New Footer About"
