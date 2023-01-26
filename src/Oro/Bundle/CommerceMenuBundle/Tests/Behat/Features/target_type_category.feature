@ticket-BB-21885
@fixture-OroCommerceMenuBundle:target_type_category/customer_user.yml
@fixture-OroCommerceMenuBundle:target_type_category/categories.yml

Feature: Target Type Category

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Ensure category has a slug
    Given I proceed as the Admin
    When I login as administrator
    And I go to Products/Master Catalog
    And I click "Category-1"
    And I fill "Category Form" with:
      | URL Slug | category-1 |
    And I submit form
    Then I should see "Category has been saved" flash message

  Scenario: Create menu item with category target type
    Given I go to System/Frontend Menus
    When click view "commerce_main_menu" in grid
    And I click "Create Menu Item"
    Then the "Target Type" field should be enabled
    When I fill "Commerce Menu Form" with:
      | Title       | CategoryTarget      |
      | Target Type | Category            |
    And I click on "JS Tree item" with title "Category-1" in element "Menu Update Category Field"
    And should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
      | 3 |
      | 4 |
      | 5 |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check menu items on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should see "CategoryTarget" button with attributes:
      | href | /category-1 |

  Scenario: Restrict category visibility
    Given I proceed as the Admin
    When I go to Products/Master Catalog
    And I click "Category-1"
    And I click "Visibility" in scrollspy
    And I click "Visibility to All" tab
    And I fill "Category Form" with:
      | Visibility To All | Hidden |
    And I submit form
    Then I should see "Category has been saved" flash message

  Scenario: Check category menu item is hidden on store front
    Given I proceed as the Buyer
    When I am on the homepage
    Then I should not see "CategoryTarget"
