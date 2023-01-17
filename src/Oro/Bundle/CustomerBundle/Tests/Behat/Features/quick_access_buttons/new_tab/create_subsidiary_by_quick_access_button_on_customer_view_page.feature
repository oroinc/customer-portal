@feature-BB-21879
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Create subsidiary by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new subsidiary from customer view page by quick access button using new tab

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "new_tab"
    And I login as administrator

  Scenario: Create subsidiary by click on quick access button
    When I go to Customers / Customers
    And I set alias "tab1" for the current browser tab
    And I click view "NoCustomerUser" in grid
    Then I should see following buttons:
      | New Subsidiary |
    When I click "New Subsidiary"
    Then a new browser tab is opened and I switch to it
    And "Customer Form" must contains values:
      | Parent Customer | NoCustomerUser |
    When I fill "Customer Form" with:
      | Name | SubsidiaryCustomerName1 |
    And I save and close form
    Then I should see Customer with:
      | Name            | SubsidiaryCustomerName1 |
      | Parent Customer | NoCustomerUser          |

  Scenario: Create subsidiary by click on quick access button from "More actions" dropdown
    When I switch to the browser tab "tab1"
    And I follow "More actions"
    And I follow "Create Subsidiary"
    Then a new browser tab is opened and I switch to it
    And "Customer Form" must contains values:
      | Parent Customer | NoCustomerUser |
    When I fill "Customer Form" with:
      | Name | SubsidiaryCustomerName2 |
    And I save and close form
    Then I should see Customer with:
      | Name            | SubsidiaryCustomerName2 |
      | Parent Customer | NoCustomerUser          |

  Scenario: Save subsidiary and return to customer view page after click on quick access button
    When I switch to the browser tab "tab1"
    And I go to Customers / Customers
    And I click view "SubsidiaryCustomerName2" in grid
    And I click "New Subsidiary"
    Then a new browser tab is opened and I switch to it
    And "Customer Form" must contains values:
      | Parent Customer | SubsidiaryCustomerName2 |
    And I should see "Save and Return" action button
    And I set alias "tab2" for the current browser tab
    When I open a new browser tab and set "tab3" alias for it
    Then I should see "Save and Return" action button
    When I switch to the browser tab "tab2"
    And I fill "Customer Form" with:
      | Name | SubsidiaryCustomerName3 |
    And I save form and return
    Then I should see "Customers / SubsidiaryCustomerName2"
    And I should see following "Subsidiaries Grid" grid:
      | Customer                | Parent Customer         |
      | SubsidiaryCustomerName3 | SubsidiaryCustomerName2 |

  Scenario: Set Customer entity view permissions to 'View:None' for Administrator Role
    When I switch to the browser tab "tab1"
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    And I click edit "Administrator" in grid
    And select following permissions:
      | Customer | View:None |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Check save and return action button is not shown on subsidiary create page
    When I switch to the browser tab "tab3"
    And I reload the page
    Then I should not see "Save and Return" action button
