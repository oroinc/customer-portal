@ticket-BB-16246
@fixture-OroProductBundle:product_collection_add.yml
Feature: Get customer entity name
  In order to get customer name
  As an Administrator
  I want to be able to get only the value of its Customer Name field

  Scenario: Feature background
    Given I login as administrator
    When I go to System/ Entities/ Entity Management
    And I filter Name as is equal to "Customer"
    And I check "OroCustomerBundle" in Module filter
    And I click view Customer in grid
    And I click "Create field"
    And I fill form with:
      | Field name   | TestField    |
      | Storage type | Table column |
      | Type         | String       |
    And I click "Continue"
    And I save and close form
    Then I should see "Field saved" flash message
    When I click update schema
    Then I should see Schema updated flash message

  Scenario: Update value of field for Customer entity
    Given I go to Customers/ Customers
    When I click edit first customer in grid
    And I fill form with:
      | TestField | Test Value |
    And I save and close form
    Then I should see "Customer has been saved" flash message

  Scenario: Create Order with right customer name field
    Given I go to Sales/ Orders
    When I click "Create Order"
    And click "Add Product"
    And fill "Order Form" with:
      | Customer      | first customer |
      | Customer User | Amanda Cole    |
      | Product       | PSKU1          |
      | Quantity      | 500            |
      | Price         | 12,99          |
    When I click "Save and Close"
    And I click "Save" in modal window
    Then I should see "Order has been saved" flash message
    And I should see Order with:
      | Customer | first customer |
