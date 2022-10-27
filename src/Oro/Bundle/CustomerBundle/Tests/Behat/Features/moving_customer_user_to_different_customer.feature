@regression
@fixture-OroCustomerBundle:CustomerUserWithTwoCustomers.yml
@fixture-OroCustomerBundle:ShoppingListFixture.yml
@fixture-OroCustomerBundle:QuoteFixture.yml
@fixture-OroCustomerBundle:OrderFixture.yml
@fixture-OroCustomerBundle:RFQFixture.yml
@fixture-OroCustomerBundle:CheckoutFixture.yml

Feature: Moving customer user to different customer
  In order to maintain the ownership of shopping lists, quotes, orders, RFQs and checkouts
  As an administrator
  I want to remove customer user from their associated entities (shopping lists, quotes, orders, RFQs and checkouts) when moving this customer user to another customer

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |
    And I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And I proceed as the Admin
    And I login as administrator

  Scenario: Try moving customer user to another customer without permissions to edit related entities
    Given I go to System/User Management/Roles
    And I click Edit "Administrator" in grid
    When select following permissions:
      | Order             | Edit:None |
      | Shopping List     | Edit:None |
      | Quote             | Edit:None |
      | Request For Quote | Edit:None |
      | Checkout          | Edit:None |
    And I save and close form
    Then I should see "Role saved"
    Then I go to Customers/Customer Users
    And I click Edit "AmandaRCole@example.org" in grid
    And I fill form with:
      | Customer | second customer |
    And I save and close form
    Then I should not see "Customer User has been saved" flash message
    And I should see "Customer User Form" validation errors:
      | Customer | Can't change customer because you don't have permissions for updating the following related entities: Request For Quote, Order, Checkout, Quote, Shopping List |

  Scenario: Check validation message for some of the related entities
    Given I go to System/User Management/Roles
    And I click Edit "Administrator" in grid
    When select following permissions:
      | Shopping List     | Edit:Global |
      | Request For Quote | Edit:Global |
      | Checkout          | Edit:Global |
    And I save and close form
    Then I should see "Role saved"
    Then I go to Customers/Customer Users
    And I click Edit "AmandaRCole@example.org" in grid
    And I fill form with:
      | Customer | second customer |
    And I save and close form
    Then I should not see "Customer User has been saved" flash message
    And I should see "Customer User Form" validation errors:
      | Customer | Can't change customer because you don't have permissions for updating the following related entities: Order, Quote |
    Then I go to System/User Management/Roles
    And I click Edit "Administrator" in grid
    When select following permissions:
      | Order    | Edit:Global |
      | Quote    | Edit:Global |
    And I save and close form
    Then I should see "Role saved"

  Scenario: Moving customer user to another customer resets customer user in associated entities
    Then I go to Customers/Customer Users
    And I click Edit "AmandaRCole@example.org" in grid
    And I fill form with:
      | Customer | second customer |
    And I save and close form
    Then I should see "Customer User has been saved" flash message

  Scenario: Check checkout after moving to another customer
    Given I proceed as the User
    And I click "Orders"
    Then there is no records in "OpenOrdersGrid"

  Scenario: Check shopping list customer user after moving to another customer
    Given I proceed as the Admin
    And I go to Sales/Shopping Lists
    And I click View "ShoppingList1" in grid
    And I should not see "Customer User Amanda Cole"

  Scenario: Check data audit is tracking resetting customer user in shopping lists after moving customer user to another customer
    When I click "Change History"
    Then I should see following "Audit History Grid" grid:
      | Old Values                                         | New Values     |
      | Customer User: Customer User "Amanda Cole" removed | Customer User: |
    And I close ui dialog

  Scenario: Check quote customer user after moving to another customer
    And go to Sales/Quotes
    And I click View "Quote1" in grid
    And I should not see "Customer User Amanda Cole"

  Scenario: Check data audit is tracking resetting customer user in quotes after moving customer user to another customer
    When I click "Change History"
    Then I should see following "Audit History Grid" grid:
      | Old Values                                         | New Values     |
      | Customer User: Customer User "Amanda Cole" removed | Customer User: |
    And I close ui dialog

  Scenario: Check order customer user after moving to another customer
    And go to Sales/Orders
    And I click View "Order1" in grid
    And I should not see "Customer User Amanda Cole"

  Scenario: Check data audit is tracking resetting customer user in orders after moving customer user to another customer
    When I click "Change History"
    Then I should see following "Audit History Grid" grid:
      | Old Values                                         | New Values     |
      | Customer User: Customer User "Amanda Cole" removed | Customer User: |
    And I close ui dialog

  Scenario: Check RFQ customer user after moving to another customer
    And go to Sales/Requests For Quote
    And I click View "0111" in grid
    And I should not see "Submitted By Amanda Cole"

  Scenario: Check data audit is tracking resetting customer user in RFQs after moving customer user to another customer
    When I click "Change History"
    Then I should see following "Audit History Grid" grid:
      | Old Values                                                       | New Values    |
      | Submitted By: Customer User "Amanda Cole" removed | Submitted By: |
    And I close ui dialog
