@regression
@ticket-BB-21885
@fixture-OroCommerceMenuBundle:target_type_none/customer_user.yml

Feature: Target Type None

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Create menu items
    Given I proceed as the Admin
    And I login as administrator
    When I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | NoneTarget |
      | Target Type | None       |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check menu items on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should see "NoneTarget"
