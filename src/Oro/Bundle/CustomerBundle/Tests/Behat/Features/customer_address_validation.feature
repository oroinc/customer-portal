@ticket-BB-14525
@ticket-BB-20875
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Customer address validation
  In order to to manage addresses for Customer
  As an administrator
  I want to see validation related to First/Last names and Organization for Address on Customer's edit page

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Create customer with address and see validation errors
    Given I proceed as the Admin
    And I login as administrator
    And I go to Customers / Customers
    And I click "Create Customer"
    When I fill form with:
      | Name            | Test customer |
      | Country         | Aland Islands |
      | Street          | Test street   |
      | City            | Test city     |
      | Zip/Postal Code | 111111        |
    And I save form
    Then I should see validation errors:
      | First Name   | First Name and Last Name or Organization should not be blank. |
      | Last Name    | Last Name and First Name or Organization should not be blank. |
      | Organization | Organization or First Name and Last Name should not be blank. |

  Scenario: Create customer with address
    When I fill form with:
      | First Name   | 0 |
      | Last Name    | 0 |
      | Organization | 0 |
    And I save and close form
    Then I should see "Customer has been saved" flash message

  Scenario: Create customer address from customer view page and see validation errors
    Given I click "New Address"
    When I fill form with:
      | Name            | New customer |
      | Country         | Germany      |
      | Street          | New street   |
      | City            | New city     |
      | Zip/Postal Code | 111111       |
      | State           | Berlin       |
    And click "Save"
    Then I should see validation errors:
      | First Name   | First Name and Last Name or Organization should not be blank. |
      | Last Name    | Last Name and First Name or Organization should not be blank. |
      | Organization | Organization or First Name and Last Name should not be blank. |

  Scenario: Create customer address from customer view page
    When I fill form with:
      | First Name   | 0 |
      | Last Name    | 0 |
      | Organization | 0 |
    And click "Save"
    Then I should see "Address saved" flash message

  Scenario: Create customer address from storefront Customer user address page and see validation errors
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And follow "Account"
    And click "Address Book"
    When click "New Company Address"
    And I fill form with:
      | Country         | Germany      |
      | Street          | New street   |
      | City            | New city     |
      | Zip/Postal Code | 111111       |
      | State           | Berlin       |
      | First Name      |              |
      | Last Name       |              |
    And click "Save"
    Then I should see validation errors:
      | First Name   | First Name and Last Name or Organization should not be blank. |
      | Last Name    | Last Name and First Name or Organization should not be blank. |
      | Organization | Organization or First Name and Last Name should not be blank. |

  Scenario: Create customer address from storefront Customer user address page with 0 as First Name, Last Names and Organization
    When I fill form with:
      | First Name   | 0 |
      | Last Name    | 0 |
      | Organization | 0 |
    And click "Save"
    Then I should see "Customer Address has been saved" flash message
