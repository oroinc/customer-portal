@regression
@ticket-BB-21885
@fixture-OroCommerceMenuBundle:target_type_system_page/customer_user.yml

Feature: Target Type System Page

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Create menu items
    Given I proceed as the Admin
    And I login as administrator
    When I go to System/Storefront Menus
    And click view "commerce_main_menu" in grid
    And I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title       | SystemPageTarget |
      | Target Type | System Page      |
      | System Page | Contact Us       |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check menu items on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should see "SystemPageTarget" button with attributes:
      | href | /contact-us |
