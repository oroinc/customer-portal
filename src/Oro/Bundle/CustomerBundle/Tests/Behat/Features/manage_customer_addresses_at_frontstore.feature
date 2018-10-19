@ticket-BB-12286
@ticket-BB-12639
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Manage Customer addresses at front-store
  In order to have ability to manage addresses
  As a Buyer
  I should have an ability to view/edit own and customer users addresses

  Scenario: Enable customer permissions
    Given I login as administrator
    And I go to Customers/ Customer User Roles
    And I click edit Buyer in grid
    And select following permissions:
      | Address               | Create:Department |
      | Address               | Edit:Department   |
      | Customer User Address | Create:User       |
      | Customer User Address | Edit:User         |
    When I save and close form
    Then I should see "Customer User Role has been saved" flash message
    And I click Logout in user menu

  Scenario: Check Buttons under DataGrids
    Given I signed in as NancyJSallee@example.org on the store frontend
    When I click "Account"
    Then I should see "Address book is empty"
    When I click "Address Book"
    Then I should see "New Address"
    And I should see "New Company Address"

  Scenario: Verify HTML tags on create new address
    Given I click "New Address"
    When I fill form with:
      | User             | Nancy Sallee               |
      | Label            | <script>alert(1)</script>  |
      | Name prefix      | <script>alert(2)</script>  |
      | First Name       | <script>alert(3)</script>  |
      | Middle Name      | <script>alert(4)</script>  |
      | Last Name        | <script>alert(5)</script>  |
      | Name suffix      | <script>alert(6)</script>  |
      | Organization     | <script>alert(7)</script>  |
      | Phone            | <script>alert(8)</script>  |
      | Street           | <script>alert(9)</script>  |
      | Street  2        | <script>alert(10)</script> |
      | City             | <script>alert(11)</script> |
      | Country          | Germany                    |
      | State            | Berlin                     |
      | Zip/Postal Code  | <script>alert(12)</script> |
      | Billing          | true                       |
      | Shipping         | true                       |
      | Default Billing  | true                       |
      | Default Shipping | true                       |
    And I click "Save"
    Then I should see following "Customer Company User Addresses Grid" grid:
      | Customer Address | City      | State  | Zip/Postal Code | Country | Type                              |
      | alert(9)         | alert(11) | Berlin | alert(12)       | Germany | Default Billing, Default Shipping |

  Scenario: Verify HTML tags on edit address
    When I click edit "alert(9)" in grid
    Then "OroForm" must contains values:
      | Label           | alert(1)  |
      | Name prefix     | alert(2)  |
      | First Name      | alert(3)  |
      | Middle Name     | alert(4)  |
      | Last Name       | alert(5)  |
      | Name suffix     | alert(6)  |
      | Organization    | alert(7)  |
      | Phone           | alert(8)  |
      | Street          | alert(9)  |
      | Street  2       | alert(10) |
      | City            | alert(11) |
      | Zip/Postal Code | alert(12) |

  Scenario: Verify address on profile page
    When I click "My Profile"
    Then I should see "alert(9)"
    And I should see "alert(11), alert(12),"
    And I click "Sign Out"

  Scenario: Check addresses of assigned user
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    And I click "Users"
    When I click view "Nancy" in grid
    Then I should see "alert(9)"
    And I should see "alert(11), alert(12),"

  Scenario: First and Last name, default for current customer user's info, during company address addition
    Given I click "Account"
    And I click "Address Book"
    And I click "New Company Address"
    Then "OroForm" must contains values:
      | First Name | Amanda  |
      | Last Name  | Cole |
