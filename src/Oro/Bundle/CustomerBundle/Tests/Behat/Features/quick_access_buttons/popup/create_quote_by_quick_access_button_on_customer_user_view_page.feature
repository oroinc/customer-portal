@feature-BB-21879
@fixture-OroProductBundle:single_product.yml

Feature: Create quote by quick access button on customer user view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new quote from customer user view page by quick access button using popup dialog

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "popup"
    And  I login as administrator

  Scenario: Create quote after the click click on quick access button on customer user view page
    When I go to Customers / Customer Users
    And I click view "AmandaRCole@example.org" in grid
    Then I should see following buttons:
      | New Quote |
    When I click "New Quote"
    Then I should see "UiDialog" with elements:
      | Title        | Create Quote |
      | okButton     | Save         |
      | cancelButton | Cancel       |
    And "Quote Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    When I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I click "Save"
    And I click "Save Button in Modal"
    Then I should see following "Quotes by Customer User Grid" grid:
      | Quote # | Step  |
      | 1       | Draft |

  Scenario: Create quote by click on quick access button from "More actions" dropdown
    When I follow "More actions"
    And I follow "Create Quote"
    Then I should see "UiDialog" with elements:
      | Title        | Create Quote |
      | okButton     | Save         |
      | cancelButton | Cancel       |
    And "Quote Form" must contains values:
      | Customer      | Company A   |
      | Customer User | Amanda Cole |
      | Website       | Default     |
    When I fill "Quote Form" with:
      | LineItemProduct | PSKU1 |
      | LineItemPrice   | 10    |
    And I click "Save"
    And I click "Save Button in Modal"
    Then I should see following "Quotes by Customer User Grid" grid:
      | Quote # | Step  |
      | 1       | Draft |
      | 2       | Draft |
