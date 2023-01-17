@ticket-BAP-20761
@regression
@elasticsearch

Feature: Parent Customer Autocomplete
  In order to mark customer as child of a another customer
  As an administrator
  I should have possibility to select right parent customer on create/edit customer page

  Scenario: Import New Customers
    Given I login as administrator
    And I go to Customers/Customers
    And I click "Import file"
    And I upload "import_customers/customers_starts_with_company_111.csv" file to "Customer Import File"
    And I click "Import file"
    Then Email should contains the following "Errors: 0 processed: 13, read: 13, added: 13, updated: 0, replaced: 0" text

  Scenario: Check Parent Customer Suggestion On Create Customer Page
    Given I click "Create Customer"
    Then I should see the following options for "Parent Customer" select pre-filled with "Company 111":
      | Company 111 |
    And I type "Company 111" into Parent Customer field to get all suggestions and see 13 suggestions

  Scenario: Check Parent Customer Suggestion On Edit Customer Page
    Given I go to Customers/Customers
    And I click Edit Company 111 in grid
    Then I should see the following options for "Parent Customer" select pre-filled with "Company 111":
      | Company 1111 |
    And I type "Company 111" into Parent Customer field to get all suggestions and see 12 suggestions

  Scenario: Check Parent Customer Suggestion On Edit Customer Page When Customer Has Children
    Given I go to Customers/Customers
    And I click Edit Company 1111 in grid
    And I fill form with:
      | Parent Customer | Company 111 |
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I go to Customers/Customers
    And I click Edit Company 111 in grid
    Then I should see the following options for "Parent Customer" select pre-filled with "Company 111":
      | Company 11111 |
    And I type "Company 111" into Parent Customer field to get all suggestions and see 11 suggestions
