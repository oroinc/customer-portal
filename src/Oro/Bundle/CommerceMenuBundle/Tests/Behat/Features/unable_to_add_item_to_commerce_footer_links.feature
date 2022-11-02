@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Unable to add item to Commerce Footer Links
  As an administrator
  I want to add new menu item to commerce footer links menu
  Menu item must appears for not logged in user

Scenario: Add new menu item
  Given sessions active:
    | Admin | first_session  |
    | Buyer | second_session |
  And I proceed as the Admin
  And I login as administrator
  And I go to System/Frontend Menus
  And click view commerce_footer_links in grid
  And I click Information in menu tree
  And I click "Create Menu Item"
  When I fill "Commerce Menu Form" with:
    | Title       | New Menu Item          |
    | Target Type | URI                    |
    | URI         | http://www.example.com |
  And I save form
  Then I should see "Menu item saved successfully." flash message

Scenario: Check added menu item
  Given I proceed as the Buyer
  When I am on the homepage
  Then I should see "New Menu Item"

Scenario: Conditions should not affect Show/Hide button
  Given I proceed as the Admin
  And I go to System/ Frontend Menus
  And click view oro_customer_menu in grid
  And I click Order History in menu tree
  And I should see following buttons:
    | Hide |
  When I type "false" in "Condition"
  And I save form
  Then I should see "Menu item saved successfully." flash message
  And I should see following buttons:
    | Hide |

Scenario: Check updated menu item
  Given I proceed as the Buyer
  And I signed in as AmandaRCole@example.org on the store frontend
  When I follow "Account"
  Then I should not see "Order History"
