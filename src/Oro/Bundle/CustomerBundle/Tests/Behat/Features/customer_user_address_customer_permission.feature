@regression
@ticket-BAP-19986
@fixture-OroCustomerBundle:CustomerUserAddressCustomerPermission.yml

Feature: Customer user address customer permission
  In order to be able correctly work with customer user permissions
  As an administrator
  I create dependents customers and check if the customer user permission tree work correctly in address book page
  and shopping list

  Scenario: Feature background
    Given sessions active:
      | Admin | system_session |
      | User  | first_session  |
    And I proceed as the Admin
    And login as administrator

  Scenario Outline: Create customers
    Given I go to Customers / Customers
    And click "Create Customer"
    When I fill form with:
      | Name | <Customer> |
    And save and close form
    Then I should see "Customer has been saved" flash message

    Examples:
      | Customer   |
      | Customer 3 |
      | Customer 2 |
      | Customer 1 |

  Scenario Outline: Update customer parents
    Given I go to Customers / Customers
    And click Edit "<Customer>" in grid
    When I fill form with:
      | Parent Customer | <Parent Customer> |
    And save and close form
    Then I should see "Customer has been saved" flash message

    Examples:
      | Customer   | Parent Customer |
      | Customer 3 | Customer 2      |
      | Customer 2 | Customer 1      |

  Scenario Outline: Create customer users
    Given I go to Customers / Customer Users
    And click "Create Customer User"
    When I fill form with:
      | First Name    | <Customer user> |
      | Last Name     | <Customer user> |
      | Email Address | <Email>         |
    And I focus on "Birthday" field
    And click "Today"
    And fill form with:
      | Password                   | Admin123   |
      | Confirm Password           | Admin123   |
      | Customer                   | <Customer> |
      | Administrator (Predefined) | true       |
    And save and close form
    Then I should see "Customer User has been saved" flash message

    Examples:
      | Customer user   | Email                             | Customer   |
      | Customer user 3 | customer_user_email_3@example.com | Customer 3 |
      | Customer user 2 | customer_user_email_2@example.com | Customer 2 |
      | Customer user 1 | customer_user_email_1@example.com | Customer 1 |

  Scenario: Check address book owner for "Customer user 1"
    Given I proceed as the User
    And I signed in as customer_user_email_1@example.com with password Admin123 on the store frontend
    And follow "Account"
    And click "Address Book"
    When I click "New Company Address"
    Then I should see the following options for "Customer" select in form "Create Address Form":
      | Customer 1 |
      | Customer 2 |
      | Customer 3 |

  Scenario: Check address book owner for "Customer user 2"
    Given I signed in as customer_user_email_2@example.com with password Admin123 on the store frontend
    And follow "Account"
    And click "Address Book"
    When I click "New Company Address"
    Then I should see the following options for "Customer" select in form "Create Address Form":
      | Customer 2 |
      | Customer 3 |

  Scenario: Check address book owner for "Customer user 3"
    Given I signed in as customer_user_email_3@example.com with password Admin123 on the store frontend
    And follow "Account"
    And click "Address Book"
    When I click "New Company Address"
    Then I should see the following options for "Customer" select in form "Create Address Form":
      | Customer 3 |

  Scenario: Check shopping list for "Customer user 3"
    Given I type "Phone" in "search"
    And click "Search Button"
    Then I should see "Phone"
    When I click "Add to Shopping List" for "Phone" product
    Then I should see "Product has been added to" flash message and I close it
    When I open shopping list widget
    And click "View Details"
    Then I should see "Phone"
