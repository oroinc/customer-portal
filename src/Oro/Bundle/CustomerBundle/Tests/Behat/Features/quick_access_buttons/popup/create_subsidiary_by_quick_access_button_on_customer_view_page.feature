@feature-BB-21879
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Create subsidiary by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new subsidiary from customer view page by quick access button using popup dialog

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "popup"
    And I login as administrator

  Scenario: Create subsidiary by click on quick access button
    When I go to Customers / Customers
    And I click view "NoCustomerUser" in grid
    Then I should see following buttons:
      | New Subsidiary |
    When I click "New Subsidiary"
    Then I should see "UiDialog" with elements:
      | Title        | Create Customer |
      | okButton     | Save            |
      | cancelButton | Cancel          |
    And "Customer Form" must contains values:
      | Parent Customer | NoCustomerUser |
    When I fill "Customer Form" with:
      | Name | SubsidiaryCustomerName1 |
    And I click "Save"
    Then I should see following "Subsidiaries Grid" grid:
      | Customer                | Parent Customer |
      | SubsidiaryCustomerName1 | NoCustomerUser  |

  Scenario: Create subsidiary by click on quick access button from "More actions" dropdown
    And I follow "More actions"
    And I follow "Create Subsidiary"
    Then I should see "UiDialog" with elements:
      | Title        | Create Customer |
      | okButton     | Save            |
      | cancelButton | Cancel          |
    And "Customer Form" must contains values:
      | Parent Customer | NoCustomerUser |
    When I fill "Customer Form" with:
      | Name | SubsidiaryCustomerName2 |
    And I click "Save"
    Then I should see following "Subsidiaries Grid" grid:
      | Customer                | Parent Customer |
      | SubsidiaryCustomerName1 | NoCustomerUser  |
      | SubsidiaryCustomerName2 | NoCustomerUser  |
