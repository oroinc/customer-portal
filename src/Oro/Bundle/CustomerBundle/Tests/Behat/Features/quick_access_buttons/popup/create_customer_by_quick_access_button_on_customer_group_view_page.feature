@feature-BB-21879

Feature: Create customer by quick access button on customer group view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new customer from customer group view page by quick access button using popup dialog

  Scenario: Choose "Open Popup Dialog" in "Quick Create Actions" system configuration
    Given I login as administrator
    When I go to System / Configuration
    And I follow "System Configuration/General Setup/Display Settings" on configuration sidebar
    Then I should see "Window Settings"
    When uncheck "Use default" for "Quick Create Actions" field
    Then Quick Create Actions field should has Replace Current Page value
    When I fill form with:
      | Quick Create Actions | Open Popup Dialog |
    And I click "Save settings"
    Then I should see "Configuration saved" flash message

  Scenario: Create customer by click on quick access button
    When I go to Customers / Customer Groups
    And I click view "Non-Authenticated Visitors" in grid
    Then I should see following buttons:
      | New Customer |
    When I click "New Customer"
    Then I should see "UiDialog" with elements:
      | Title        | Create Customer |
      | okButton     | Save            |
      | cancelButton | Cancel          |
    And "Customer Form" must contains values:
      | Group | Non-Authenticated Visitors |
    When I fill "Customer Form" with:
      | Name | CustomerName1 |
    And I click "Save"
    Then I should see following "Customer by Customer Group Grid" grid:
      | Name          | Parent Customer  | Account       |
      | CustomerName1 |                  | CustomerName1 |

  Scenario: Create customer by click on quick access button in dropdown on customer group view page
    When I click "Create Customer"
    Then I should see "UiDialog" with elements:
      | Title        | Create Customer |
      | okButton     | Save            |
      | cancelButton | Cancel          |
    And "Customer Form" must contains values:
      | Group | Non-Authenticated Visitors |
    When I fill "Customer Form" with:
      | Name | CustomerName2 |
    And I click "Save"
    Then I should see following "Customer by Customer Group Grid" grid:
      | Name          | Parent Customer  | Account       |
      | CustomerName1 |                  | CustomerName1 |
      | CustomerName2 |                  | CustomerName2 |
