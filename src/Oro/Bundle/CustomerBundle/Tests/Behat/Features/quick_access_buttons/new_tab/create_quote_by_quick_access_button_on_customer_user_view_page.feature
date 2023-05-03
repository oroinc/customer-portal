@feature-BB-21879
@fixture-OroProductBundle:single_product.yml

Feature: Create quote by quick access button on customer user view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new quote from customer user view page by quick access button using new tab

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "new_tab"
    And I login as administrator

  Scenario: Create quote after the click click on quick access button on customer user view page
    When I go to Customers / Customer Users
    And I set alias "tab1" for the current browser tab
    And I click view "AmandaRCole@example.org" in grid
    Then I should see following buttons:
      | New Quote |
    When I click "New Quote"
    Then a new browser tab is opened and I switch to it
    And "Quote Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    When I click "Line Items"
    And I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I save and close form
    And I click "Save" in modal window
    Then I should see Quote with:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |

  Scenario: Create quote by click on quick access button from "More actions" dropdown
    When I switch to the browser tab "tab1"
    And I follow "More actions"
    And I follow "Create Quote"
    Then a new browser tab is opened and I switch to it
    And "Quote Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    When I click "Line Items"
    And I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I save and close form
    And I click "Save" in modal window
    Then I should see Quote with:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |

  Scenario: Save quote and return to customer user view page after click on quick access button
    When I switch to the browser tab "tab1"
    And I click "New Quote"
    Then a new browser tab is opened and I switch to it
    And "Quote Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    And I should see "Save and Return" action button
    And I set alias "tab2" for the current browser tab
    When I open a new browser tab and set "tab3" alias for it
    Then I should see "Save and Return" action button
    When I switch to the browser tab "tab2"
    And I click "Line Items"
    And I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I save form and return
    And I click "Save" in modal window
    Then I should see "Customer Users / AmandaRCole@example.org"
    And I should see following "Quotes by Customer User Grid" grid:
      | Quote # | Step  |
      | 1       | Draft |
      | 2       | Draft |
      | 3       | Draft |

  Scenario: Set Customer User entity view permissions to 'View:None' for Administrator Role
    When I switch to the browser tab "tab1"
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    And I click edit "Administrator" in grid
    And select following permissions:
      | Customer User | View:None |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Check save and return action button is not shown on quote create page
    When I switch to the browser tab "tab3"
    And I reload the page
    Then I should not see "Save and Return" action button
