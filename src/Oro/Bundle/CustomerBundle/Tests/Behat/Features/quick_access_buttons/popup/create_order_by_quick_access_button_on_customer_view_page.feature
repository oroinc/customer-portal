@feature-BB-21879
@fixture-OroProductBundle:single_product.yml

Feature: Create order by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new order from customer view page by quick access button using popup dialog

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "popup"
    And  I login as administrator

  Scenario: Create order by click on quick access button
    When I go to Customers / Customers
    And I click view "Company A" in grid
    Then I should see following buttons:
      | New Order |
    When I click "New Order"
    Then I should see "UiDialog" with elements:
      | Title        | Create Order |
      | okButton     | Save         |
      | cancelButton | Cancel       |
    And "Order Form" must contains values:
      | Customer | Company A |
      | Website  | Default   |
    When I click "Add Product"
    And fill "Order Form" with:
      | Product                     | PSKU1                 |
      | Quantity                    | 10                    |
      | Price                       | 10                    |
      | Billing Address Label       | Order1 with Company A |
      | Billing Address First name  | Company               |
      | Billing Address Last name   | A                     |
      | Billing Address Country     | Australia             |
      | Billing Address Street      | Oxford Street         |
      | Billing Address City        | Sydney                |
      | Billing Address State       | New South Wales       |
      | Billing Address Postal Code | B1P 4C4               |
    And I click "Save"
    And I click "Save Button in Modal"
    Then I should see following "Sales Orders Grid" grid:
      | Order Number | Internal Status | Total   |
      | 1            | Open            | $100.00 |

  Scenario: Create order by click on quick access button from "More actions" dropdown
    When I follow "More actions"
    And I follow "Create Order"
    Then I should see "UiDialog" with elements:
      | Title        | Create Order |
      | okButton     | Save         |
      | cancelButton | Cancel       |
    And "Order Form" must contains values:
      | Customer | Company A |
      | Website  | Default   |
    When I click "Add Product"
    And fill "Order Form" with:
      | Product                     | PSKU1                 |
      | Quantity                    | 1                     |
      | Price                       | 9.99                  |
      | Billing Address Label       | Order2 with Company A |
      | Billing Address First name  | Company               |
      | Billing Address Last name   | A                     |
      | Billing Address Country     | Australia             |
      | Billing Address Street      | Oxford Street         |
      | Billing Address City        | Sydney                |
      | Billing Address State       | New South Wales       |
      | Billing Address Postal Code | B1P 4C4               |
    And I click "Save"
    And I click "Save Button in Modal"
    Then I should see following "Sales Orders Grid" grid:
      | Order Number | Internal Status | Total   |
      | 2            | Open            | $9.99   |
      | 1            | Open            | $100.00 |
