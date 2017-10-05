Feature: Featured menu, displayed on the main front page

  Scenario: Create new menu item and use it on front side
    Given I login as administrator
    And I go to System/Frontend Menus
    And click view featured_menu in grid
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | Test Item        |
      | URI         | /admin/          |
      | Description | test description |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I am on the homepage
    And I scroll to text "VIEW Test Item"
    And I click "VIEW Test Item"
    Then I should be on Admin Dashboard page

  Scenario: Edit already existing menu item and use it on front side
    Given I go to System/Frontend Menus
    And click view featured_menu in grid
    And I click "Test Item"
    When I fill "Commerce Menu Form" with:
      | Title | Featured Item_0 |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I am on the homepage
    And I scroll to text "VIEW Featured Item_0"
    And I click "VIEW Featured Item_0"
    Then I should be on Admin Dashboard page

  Scenario: Change sequence of menu items
    Given I go to System/Frontend Menus
    And click view featured_menu in grid
    And I click "Create Menu Item"
    When I fill "Commerce Menu Form" with:
      | Title       | Featured Item_1        |
      | URI         | http://www.example.com |
      | Description | test description       |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I expand "featured_menu" in tree
    And I click "Featured Item_1"
    And I drag and drop "Featured Item_1" before "Featured Item_0"
    When I click "Save"
    Then I should see "Featured Item_0" after "Featured Item_1" in tree

  Scenario: Check visibility option for unauthorized users
    Given I click "Featured Item_0"
    When I fill "Commerce Menu Form" with:
      | Condition | is_logged_in() |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I am on the homepage
    Then I should not see "Featured Item_0"
