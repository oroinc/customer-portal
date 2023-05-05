@feature-BB-21879
@fixture-OroProductBundle:single_product.yml

Feature: Create order by quick access button on customer user view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new order from customer user view page by quick access button using new tab

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "new_tab"
    And I login as administrator

  Scenario: Create order by click on quick access button
    When I go to Customers / Customer Users
    And I set alias "tab1" for the current browser tab
    And I click view "AmandaRCole@example.org" in grid
    Then I should see following buttons:
      | New Order |
    When I click "New Order"
    Then a new browser tab is opened and I switch to it
    And "Order Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    When I click "Add Product"
    And fill "Order Form" with:
      | Product                     | PSKU1                   |
      | Quantity                    | 10                      |
      | Price                       | 10                      |
      | Billing Address Label       | Order1 with Amanda Cole |
      | Billing Address First name  | Amanda                  |
      | Billing Address Last name   | Cole                    |
      | Billing Address Country     | Australia               |
      | Billing Address Street      | Oxford Street           |
      | Billing Address City        | Sydney                  |
      | Billing Address State       | New South Wales         |
      | Billing Address Postal Code | B1P 4C4                 |
    And I save and close form
    And I click "Save" in modal window
    Then I should see Order with:
      | Billing Address | Order1 with Amanda Cole |
      | Customer        | Company A               |
      | Customer User   | Amanda Cole             |
      | Website         | Default                 |

  Scenario: Create order by click on quick access button from "More actions" dropdown
    When I switch to the browser tab "tab1"
    And I follow "More actions"
    And I click "Create Order"
    Then a new browser tab is opened and I switch to it
    And "Order Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    When I click "Add Product"
    And fill "Order Form" with:
      | Product                     | PSKU1                   |
      | Quantity                    | 10                      |
      | Price                       | 10                      |
      | Billing Address Label       | Order2 with Amanda Cole |
      | Billing Address First name  | Amanda                  |
      | Billing Address Last name   | Cole                    |
      | Billing Address Country     | Australia               |
      | Billing Address Street      | Oxford Street           |
      | Billing Address City        | Sydney                  |
      | Billing Address State       | New South Wales         |
      | Billing Address Postal Code | B1P 4C4                 |
    And I save and close form
    And I click "Save" in modal window
    Then I should see Order with:
      | Billing Address | Order2 with Amanda Cole |
      | Customer        | Company A               |
      | Customer User   | Amanda Cole             |
      | Website         | Default                 |

  Scenario: Save order and return to customer user view page after click on quick access button
    When I switch to the browser tab "tab1"
    And I click "New Order"
    Then a new browser tab is opened and I switch to it
    And "Order Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    And I should see "Save and Return" action button
    And I set alias "tab2" for the current browser tab
    When I open a new browser tab and set "tab3" alias for it
    Then I should see "Save and Return" action button
    When I switch to the browser tab "tab2"
    And I click "Add Product"
    And fill "Order Form" with:
      | Product                     | PSKU1                   |
      | Quantity                    | 1                       |
      | Price                       | 9.99                    |
      | Billing Address Label       | Order3 with Amanda Cole |
      | Billing Address First name  | Amanda                  |
      | Billing Address Last name   | Cole                    |
      | Billing Address Country     | Australia               |
      | Billing Address Street      | Oxford Street           |
      | Billing Address City        | Sydney                  |
      | Billing Address State       | New South Wales         |
      | Billing Address Postal Code | B1P 4C4                 |
    And I save form and return
    And I click "Save" in modal window
    Then I should see "Customer Users / AmandaRCole@example.org"
    And I should see following "Customer User Sales Orders Grid" grid:
      | Order Number | Internal Status | Total   |
      | 3            | Open            | $9.99   |
      | 2            | Open            | $100.00 |
      | 1            | Open            | $100.00 |

  Scenario: Set Customer User entity view permissions to 'View:None' for Administrator Role
    When I switch to the browser tab "tab1"
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    And I click edit "Administrator" in grid
    And select following permissions:
      | Customer User | View:None |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Check save and return action button is not shown on order create page
    When I switch to the browser tab "tab3"
    And I reload the page
    Then I should not see "Save and Return" action button
