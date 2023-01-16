@feature-BB-21879
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Create opportunity by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new opportunity from customer view page by quick access button using new tab

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "new_tab"
    And I login as administrator

  Scenario: Create opportunity by click on quick access button on customer view page
    When I go to Customers / Customers
    And I set alias "tab1" for the current browser tab
    And I click view "NoCustomerUser" in grid
    Then I should see following buttons:
      | New Opportunity |
    When I click "New Opportunity"
    Then a new browser tab is opened and I switch to it
    And "Opportunity Form" must contains values:
      | Account | NoCustomerUser |
    When I fill "Opportunity Form" with:
      | Opportunity Name | Opportunity 1 |
    And I save and close form
    Then I should see opportunity with:
      | Opportunity Name | Opportunity 1  |
      | Account          | NoCustomerUser |

  Scenario: Create opportunity by click on quick access button from "More actions" dropdown
    When I switch to the browser tab "tab1"
    And I follow "More actions"
    And I follow "Create Opportunity"
    Then a new browser tab is opened and I switch to it
    And "Opportunity Form" must contains values:
      | Account | NoCustomerUser |
    When I fill "Opportunity Form" with:
      | Opportunity Name | Opportunity 2 |
    And I save and close form
    Then I should see opportunity with:
      | Opportunity Name | Opportunity 2  |
      | Account          | NoCustomerUser |

  Scenario: Save opportunity and return to customer view page after click on quick access button
    When I switch to the browser tab "tab1"
    And I click "New Opportunity"
    Then a new browser tab is opened and I switch to it
    And "Opportunity Form" must contains values:
      | Account | NoCustomerUser |
    And I should see "Save and Return" action button
    And I set alias "tab2" for the current browser tab
    When I open a new browser tab and set "tab3" alias for it
    Then I should see "Save and Return" action button
    When I switch to the browser tab "tab2"
    And I fill "Opportunity Form" with:
      | Opportunity Name | Opportunity 3 |
    And I save form and return
    Then I should see "Customers / NoCustomerUser"
    And I should see following "Opportunities by Customer Grid" grid:
      | Opportunity Name | Status |
      | Opportunity 3    | Open   |
      | Opportunity 2    | Open   |
      | Opportunity 1    | Open   |

  Scenario: Set Customer entity view permissions to 'View:None' for Administrator Role
    When I switch to the browser tab "tab1"
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    And I click edit "Administrator" in grid
    And select following permissions:
      | Customer | View:None |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Check save and return action button is not shown on opportunity create page
    When I switch to the browser tab "tab3"
    And I reload the page
    Then I should not see "Save and Return" action button
