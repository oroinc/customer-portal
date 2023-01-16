@feature-BB-21879
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Create opportunity by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new opportunity from customer view page by quick access button using popup dialog

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "popup"
    And I login as administrator

  Scenario: Create opportunity by click on quick access button on customer view page
    When I go to Customers / Customers
    And I click view "NoCustomerUser" in grid
    Then I should see following buttons:
      | New Opportunity |
    When I click "New Opportunity"
    Then I should see "UiDialog" with elements:
      | Title        | Create Opportunity |
      | okButton     | Save               |
      | cancelButton | Cancel             |
    And "Opportunity Form" must contains values:
      | Account | NoCustomerUser |
    When I fill "Opportunity Form" with:
      | Opportunity Name | Opportunity 1 |
    And I click "Save"
    Then I should see following "Opportunities by Customer Grid" grid:
      | Opportunity Name | Status |
      | Opportunity 1    | Open   |

  Scenario: Create opportunity by click on quick access button from "More actions" dropdown
    When I follow "More actions"
    And I follow "Create Opportunity"
    Then I should see "UiDialog" with elements:
      | Title        | Create Opportunity |
      | okButton     | Save               |
      | cancelButton | Cancel             |
    And "Opportunity Form" must contains values:
      | Account | NoCustomerUser |
    When I fill "Opportunity Form" with:
      | Opportunity Name | Opportunity 2 |
    And I click "Save"
    Then I should see following "Opportunities by Customer Grid" grid:
      | Opportunity Name | Status |
      | Opportunity 2    | Open   |
      | Opportunity 1    | Open   |
