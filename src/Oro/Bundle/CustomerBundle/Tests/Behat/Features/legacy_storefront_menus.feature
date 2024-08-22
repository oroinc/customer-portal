@regression
@ticket-BB-24264

Feature: Legacy storefront menus
  Some storefront menus remain only in order to support old storefront themes.
  The new themes either use some different menu instead, or uses no menu at all (e.g. when the "menu" was replaced
  with links in an editable content block, etc.).
  To help admins better understand what is going on, we explain why such menus are preserved,
  and (if applicable) what other menu should be used instead.

  Scenario: Check legacy storefront menus
    Given I login as administrator

    When I go to System / Frontend Menus
    And I click "view" on row "customer_usermenu" in grid
    Then I should see "This menu applies to OroCommerce version 5.1 and below and is retained in the current version only for backward compatibility with legacy storefront themes."

    When I go to System / Frontend Menus
    And I click "view" on row "oro_customer_menu" in grid
    Then I should see "This menu applies to OroCommerce version 5.1 and below and is retained in the current version only for backward compatibility with legacy storefront themes. For v6.0 and above, please use the oro_customer_menu_refreshing_teal menu to modify the values in the Account section of the user menu."
