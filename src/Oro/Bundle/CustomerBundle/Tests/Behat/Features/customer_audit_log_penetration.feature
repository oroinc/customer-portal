@regression
@ticket-BAP-21460
@fixture-OroCustomerBundle:CompanyA.yml
@fixture-OroCustomerBundle:SubsidiaryCustomerFixture.yml

Feature: Customer Audit Log Penetration
  In order to manage change history between related entity
  As an administrator
  I want to be able to see audit logs append to related entity by Append Audit Log To The Related Entity is Yes

  Scenario: Update parent customer will not append audit log to it's children customers
    Given I login as administrator
    When I go to Customers / Customers
    And I click Edit Company A in grid
    And I fill form with:
      | Tax Code | Tax_code_1 |
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values         | New values                                               |
      | Customer Tax Code: | Customer Tax Code:  Customer Tax Code "Tax_code_1" added |
    And I close ui dialog
    When I go to Customers / Customers
    And I click View Company A - East Division in grid
    When I click "Change History"
    Then number of records in "Audit History Grid" should be 0
    And I close ui dialog
    When I go to Customers / Customers
    And I click View Company A - West Division in grid
    When I click "Change History"
    Then number of records in "Audit History Grid" should be 0
    And I close ui dialog

  Scenario: Update child customer will not append audit log to it's parent customer
    Given I go to Customers / Customers
    And I click Edit Company A - East Division in grid
    And I fill form with:
      | Tax Code | Tax_code_2 |
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values                                                | New values                                               |
      | Customer Tax Code: Customer Tax Code "Tax_code_1" removed | Customer Tax Code:  Customer Tax Code "Tax_code_2" added |
    And I close ui dialog
    When I go to Customers / Customers
    And I click View Company A in grid
    When I click "Change History"
    Then number of records in "Audit History Grid" should be 1
    And I close ui dialog

  Scenario: Set option Append Audit Log To The Related Entity to Yes
    And I go to System/Entities/Entity Management
    And filter Name as is equal to "Customer"
    And click View Customer in grid
    And click Edit parent in grid
    And I fill form with:
      | Append Audit Log To The Related Entity | Yes |
    And I save and close form
    Then I should see "Field saved" flash message
    And click Edit children in grid
    And I fill form with:
      | Append Audit Log To The Related Entity | Yes |
    And I save and close form
    Then I should see "Field saved" flash message

  Scenario: Update parent customer will append audit log to it's children customers
    Given I go to Customers / Customers
    And I click Edit Company A in grid
    And I fill form with:
      | Tax Code | Tax_code_3 |
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values                                                | New values                                               |
      | Customer Tax Code: Customer Tax Code "Tax_code_1" removed | Customer Tax Code:  Customer Tax Code "Tax_code_3" added |
    And I close ui dialog
    When I go to Customers / Customers
    And I click View Company A - East Division in grid
    When I click "Change History"
    Then number of records in "Audit History Grid" should be 2
    And I close ui dialog
    When I go to Customers / Customers
    And I click View Company A - West Division in grid
    When I click "Change History"
    Then number of records in "Audit History Grid" should be 1
    Then should see following "Audit History Grid" grid:
      | Old Values                                                                              | New values                                                                               |
      | Parent Customer: Customer "Company A" changed: Customer Tax Code: Customer Tax Code "1" | Parent Customer:  Customer "Company A" changed: Customer Tax Code: Customer Tax Code "3" |
    And I close ui dialog

  Scenario: Update child customer will append audit log to it's parent customer
    Given I go to Customers / Customers
    And I click Edit Company A - East Division in grid
    And I fill form with:
      | Tax Code | Tax_code_1 |
    And I save and close form
    Then I should see "Customer has been saved" flash message
    When I click "Change History"
    Then should see following "Audit History Grid" grid:
      | Old Values                                                | New values                                               |
      | Customer Tax Code: Customer Tax Code "Tax_code_2" removed | Customer Tax Code:  Customer Tax Code "Tax_code_1" added |
    And I close ui dialog
    When I go to Customers / Customers
    And I click View Company A in grid
    When I click "Change History"
    Then number of records in "Audit History Grid" should be 3
    Then should see following "Audit History Grid" grid:
      | Old Values                                                                                           | New values                                                                                            |
      | Subsidiaries: Customer "Company A - East Division" changed: Customer Tax Code: Customer Tax Code "2" | Subsidiaries:  Customer "Company A - East Division" changed: Customer Tax Code: Customer Tax Code "1" |
    And I close ui dialog
