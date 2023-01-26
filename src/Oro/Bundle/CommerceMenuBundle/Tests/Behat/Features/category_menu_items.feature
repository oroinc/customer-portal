@ticket-BB-21885
@fixture-OroCommerceMenuBundle:category_menu_items/customer_user.yml
@fixture-OroCommerceMenuBundle:category_menu_items/categories.yml

Feature: Category Menu Items

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |
    And I proceed as the Admin
    And I login as administrator

  Scenario: Hide system category menu item
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I click on "Category-1" in tree "Sidebar Menu Tree"
    And I click "Hide"

  Scenario: Check that the hidden category menu item is not displayed on storefront
    Given I proceed as the Buyer
    When I signed in as AmandaRCole@example.org on the store frontend
    Then I should not see "Category-1" in main menu
    And I should see "Category-2" in main menu

  Scenario: Check form fields
    Given I proceed as the Admin
    And I click on "commerce_main_menu" in tree "Sidebar Menu Tree"
    When I click "Create Menu Item"
    And should see the following options for "Menu Template" select:
      | Flat menu, up to 2 levels deep |
      | Tree, up to 3 levels deep      |
      | Mega menu, up to 4 levels deep |

  Scenario: Create new category menu item
    When I fill "Commerce Menu Form" with:
      | Title       | My-Category |
      | Target Type | Category    |
    And I expand "Category-1" in tree "Menu Update Category Field"
    And I click on "Category-1-1" in tree "Menu Update Category Field"
    And I save form
    Then I should see "Menu item saved successfully." flash message
    And I should see "My-Category" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"

  Scenario: Check that the created category menu item does not have children
    When I expand "My-Category" in tree "Sidebar Menu Tree"
    Then I should not see "Category-1-1-1" belongs to "My-Category" in tree "Sidebar Menu Tree"

  Scenario: Check that the created category menu item is displayed in menu on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "Category-2" in main menu
    And I should see "My-Category" in main menu
    And I should not see "Category-1" in main menu
    And I should not see "Category-1 / Category-1-1" in main menu
    And I should not see "My-Category / Category-1-1-1" in main menu

  Scenario: Set "Menu Template"
    Given I proceed as the Admin
    And I click on "My-Category" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Menu Template | Tree, up to 3 levels deep |
    And I save form

  Scenario: Increase "Max Traverse Level" to 3
    And I click on "My-Category" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Max Traverse Level | 3 |
    And I save form

  Scenario: Check that the created category menu item has children up to 3rd level
    When I expand "My-Category" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1" belongs to "My-Category" in tree "Sidebar Menu Tree"
    When I expand "Category-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1-1" belongs to "Category-1-1-1" in tree "Sidebar Menu Tree"
    When I expand "Category-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1-1-1" belongs to "Category-1-1-1-1" in tree "Sidebar Menu Tree"
    When I expand "Category-1-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should not see "Category-1-1-1-1-1-1" belongs to "Category-1-1-1-1-1" in tree "Sidebar Menu Tree"

  Scenario: Check that the created category menu item is displayed in menu using menu template on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "My-Category / Category-1-1-1 / Category-1-1-1-1 / Category-1-1-1-1-1" in main menu

  Scenario: Decrease "Max Traverse Level" of child category to 0
    Given I proceed as the Admin
    When I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    And I click on "Category-1-1-1" in tree "Sidebar Menu Tree"
    Then should see the following options for "Max Traverse Level" select:
      | 0 |
      | 1 |
      | 2 |
    When I fill "Commerce Menu Form" with:
      | Max Traverse Level | 0 |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Check that the child category menu item has no children
    When I expand "My-Category" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1" belongs to "My-Category" in tree "Sidebar Menu Tree"
    When I expand "Category-1-1-1" in tree "Sidebar Menu Tree"
    Then I should not see "Category-1-1-1-1" belongs to "Category-1-1-1" in tree "Sidebar Menu Tree"

  Scenario: Check that the child category menu item is displayed without children in menu on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "My-Category / Category-1-1-1" in main menu
    And I should not see "My-Category / Category-1-1-1 / Category-1-1-1-1" in main menu

  Scenario: Check that the label of category tree menu item can be changed
    Given I proceed as the Admin
    And I click on "Category-1-1-1" in tree "Sidebar Menu Tree"
    When I fill "Commerce Menu Form" with:
      | Title | Category-1-1-1-upd |
    And I save form
    Then I should see "Category-1-1-1-upd" belongs to "My-Category" in tree "Sidebar Menu Tree"

  Scenario: Create custom menu item inside the category menu items tree
    When I click "Create Menu Item"
    And I fill "Commerce Menu Form" with:
      | Title  | Custom-1-1-1        |
      | Target | URI                 |
      | URI    | https://example.org |
    And I save form
    Then I should see "Custom-1-1-1" belongs to "Category-1-1-1-upd" in tree "Sidebar Menu Tree"

  Scenario: Check that the category tree menu item can be moved outside of its parent
    Given I expand "My-Category" in tree "Sidebar Menu Tree"
    When I move "Category-1-1-1-upd" before "Category-2" in tree "Sidebar Menu Tree"
    And I reload the page
    Then I should see "Category-1-1-1-upd" belongs to "commerce_main_menu" in tree "Sidebar Menu Tree"
    And I should see "Custom-1-1-1" belongs to "Category-1-1-1-upd" in tree "Sidebar Menu Tree"

  Scenario: Check max traverse level can be increased to 5
    When I click on "Category-1-1-1-upd" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Max Traverse Level | 5                         |
      | Menu Template      | Tree, up to 3 levels deep |
    And I save form
    Then I should see "Menu item saved successfully." flash message
    When I expand "Category-1-1-1-upd" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1-1" belongs to "Category-1-1-1-upd" in tree "Sidebar Menu Tree"
    When I expand "Category-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1-1-1" belongs to "Category-1-1-1-1" in tree "Sidebar Menu Tree"
    When I expand "Category-1-1-1-1-1" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1-1-1-1" belongs to "Category-1-1-1-1-1" in tree "Sidebar Menu Tree"

  Scenario: Check that the category tree menu item moved outside of its parent is displayed on storefront
    Given I proceed as the Buyer
    When I reload the page
    Then I should see "Category-1-1-1-upd" in main menu
    And I should see "Category-1-1-1-upd / Custom-1-1-1" in main menu
    And I should not see "My-Category / Category-1-1-1" in main menu
    And I should not see "My-Category / Category-1-1-1-upd" in main menu

  Scenario: Update category title
    Given I proceed as the Admin
    When go to Products/ Master Catalog
    And I expand "Category-1" in tree
    And I expand "Category-1-1" in tree
    And I expand "Category-1-1-1" in tree
    And I click on "Category-1-1-1-1" in tree
    And fill "Category Form" with:
      | Title | Category-1-1-1-1-changed |
    And click "Save"
    Then I should see "Category has been saved" flash message

  Scenario: Check that the title of menu item follows category title
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    When I expand "Category-1-1-1-upd" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1-1-changed" belongs to "Category-1-1-1-upd" in tree
    And I should not see "Category-1-1-1-1" belongs to "Category-1-1-1-upd" in tree

  Scenario: Update category menu item title
    When I click on "Category-1-1-1-1-changed" in tree "Sidebar Menu Tree"
    And I fill "Commerce Menu Form" with:
      | Title | Category-1-1-1-1-changed-frozen |
    And I save form
    Then I should see "Menu item saved successfully." flash message

  Scenario: Update category title again
    When go to Products/ Master Catalog
    And I expand "Category-1" in tree
    And I expand "Category-1-1" in tree
    And I expand "Category-1-1-1" in tree
    And I click on "Category-1-1-1-1" in tree
    And fill "Category Form" with:
      | Title | Category-1-1-1-1-changed-2 |
    And click "Save"
    Then I should see "Category has been saved" flash message

  Scenario: Check that the title of menu item does not follow the category title anymore
    Given I go to System/Frontend Menus
    And click view "commerce_main_menu" in grid
    When I expand "Category-1-1-1-upd" in tree "Sidebar Menu Tree"
    Then I should see "Category-1-1-1-1-changed-frozen" belongs to "Category-1-1-1-upd" in tree
    And I should not see "Category-1-1-1-1-changed" belongs to "Category-1-1-1-upd" in tree

  Scenario: Check that the category menu item can be moved inside another one
    When I move "Category-2" into "My-Category" in tree "Sidebar Menu Tree"
    And I expand "My-Category" in tree "Sidebar Menu Tree"
    And I move "About" before "Category-2" in tree "Sidebar Menu Tree"
    And I reload the page
    And I expand "My-Category" in tree "Sidebar Menu Tree"
    Then I should see "About" belongs to "My-Category" in tree "Sidebar Menu Tree"
    And I should see "Category-2" after "About" in tree "Sidebar Menu Tree"
