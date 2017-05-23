Feature: Unable to add item to Commerce Footer Links
  As an administrator
  I want to add new menu item to commerce footer links menu
  Menu item must appears for not logged in user

Scenario: Add new menu item
  Given I login as administrator
  And I go to System/Frontend Menus
  And click view commerce_footer_links in grid
  And I click Information in menu tree
  And I click "Create Menu Item"
  When I fill "Commerce Menu Form" with:
    | Title | New Menu Item          |
    | URI   | http://www.example.com |
  And I save form
  Then I should see "Menu item saved successfully." flash message
  When I am on the homepage
  Then I should see "New Menu Item"
