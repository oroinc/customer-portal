@ticket-BB-12286
@waf-skip
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroCustomerBundle:CustomerAddressFixtureBB12286.yml

Feature: Manage Customer previous addresses at front-store
  In order to have ability to manage addresses
  As a Buyer
  I should have an ability to view/edit own addresses

  Scenario: Enable customer permissions
    Given I login as administrator
    And I go to Customers/ Customer User Roles
    And I click edit Buyer in grid
    And select following permissions:
      | Customer Address      | Create:Department (Same Level) |
      | Customer Address      | Edit:Department (Same Level)   |
      | Customer User Address | Create:User (Own)              |
      | Customer User Address | Edit:User (Own)                |
    When I save and close form
    Then I should see "Customer User Role has been saved" flash message
    And I click Logout in user menu

  Scenario: Check my previous address on profile page
    Given I signed in as AmandaRCole@example.org on the store frontend
    When I follow "Account"
    Then I should see "<script>alert(9)</script>"
    And I should see "<script>alert(11)</script>, <script>alert(12)</script>, DE-BE"

  Scenario: Check my previous address at Address Book
    When I click "Address Book"
    Then I should see following "Customer Company User Addresses Grid" grid:
      | Customer Address          | City                       | State  | Zip/Postal Code            | Country | Type {{ "type": "array" }}        |
      | <script>alert(9)</script> | <script>alert(11)</script> | Berlin | <script>alert(12)</script> | Germany | Default Shipping, Default Billing |

  Scenario: Check my previous address on edit page
    When I click edit "<script>alert(9)</script>" in grid
    Then "OroForm" must contains values:
      | Label           | <script>alert(1)</script>  |
      | Name prefix     | <script>alert(2)</script>  |
      | First Name      | <script>alert(3)</script>  |
      | Middle Name     | <script>alert(4)</script>  |
      | Last Name       | <script>alert(5)</script>  |
      | Name suffix     | <script>alert(6)</script>  |
      | Organization    | <script>alert(7)</script>  |
      | Phone           | <script>alert(8)</script>  |
      | Street          | <script>alert(9)</script>  |
      | Street  2       | <script>alert(10)</script> |
      | City            | <script>alert(11)</script> |
      | Zip/Postal Code | <script>alert(12)</script> |
    And I fill form with:
      | City | <script>alert(11)</script> |

    When I click "Save"
    Then I should see following "Customer Company User Addresses Grid" grid:
      | Customer Address | City      | State  | Zip/Postal Code | Country | Type {{ "type": "array" }}        |
      | alert(9)         | alert(11) | Berlin | alert(12)       | Germany | Default Shipping, Default Billing |
