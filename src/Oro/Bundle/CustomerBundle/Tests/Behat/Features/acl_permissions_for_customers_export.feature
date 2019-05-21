@fixture-OroCustomerAccountBridgeBundle:ExportCustomerFixture.yml
@fixture-OroUserBundle:manager.yml
Feature: ACL permissions for customers export
  In order to export customers
  As an Administrator
  I want to be able to manage accessibility for download export files

  Scenario: Feature background
    Given sessions active:
      | Ethan   | first_session  |
      | Admin   | system_session |

  Scenario: Change Sales Manager Import/Export result permissions
    Given I proceed as the Admin
    When I login as administrator
    And I go to System/User Management/Roles
    And click edit "Sales Manager" in grid
    And select following permissions:
      | Import/Export result | View:User |
    And I save and close form
    And I go to Customers/Customers

  Scenario: Export Customers and check permissions
    Given I click "Export"
    Then I should see "Export started successfully. You will receive email notification upon completion." flash message
    And Email should contains the following "Export performed successfully. 6 customers were exported. Download" text
    And Exported file for "Customers" contains at least the following columns:
      | Id  | Name                       |  Parent Id   |  Group Name         |Owner Id| Tax code   | Account Id  | Internal rating Id | Payment term Label |
      |  1  | Company A                  |              | All Customers       | 1      | Tax_code_1 | 1           | 2_of_5             |        net 30      |
      |  2  | Company A - East Division  |  1           | All Customers       | 1      | Tax_code_1 | 2           | 1_of_5             |        net 90      |
      |  3  | Company A - West Division  |  1           | All Customers       | 1      | Tax_code_1 | 3           | 1_of_5             |        net 60      |
      |  4  | Customer G                 |              | Wholesale Customers | 2      | Tax_code_3 | 4           | 3_of_5             |        net 60      |
      |  5  | Partner C                  |              | Partners            | 2      | Tax_code_3 | 5           | 4_of_5             |        net 30      |
      |  6  | Wholesaler B               |              | All Customers       | 2      | Tax_code_2 | 6           | 4_of_5             |        net 60      |
    And An email containing the following was sent:
      | To   | admin@example.com                                                  |
      | Body | Export performed successfully. 6 customers were exported. Download |
    When I follow "Download" link from the email
    Then I should not see "403. Forbidden You don't have permission to access this page."
    # Check Sales Manager permissions
    When I proceed as the Ethan
    And I login as "ethan" user
    And I follow "Download" link from the email
    Then I should see "403. Forbidden You don't have permission to access this page."
