@ticket-BB-21189

Feature: Export with no Customers
  In order to export list of customers
  As an Administrator
  I want to have the Export button on the Customers -> Customers page workable when there are no customers

  Scenario: Export Customers
    Given I login as administrator
    And I go to Customers/Customers
    When I click "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "No customers found for export." text
