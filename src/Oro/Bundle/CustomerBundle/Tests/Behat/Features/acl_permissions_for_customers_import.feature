@fixture-OroCustomerAccountBridgeBundle:ImportCustomerFixture.yml
@fixture-OroUserBundle:manager.yml
Feature: ACL permissions for customers import
  In order to import customers
  As an Administrator
  I want to have possibility to manage accessibility for view import error logs files

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

  Scenario: Prepare for Customers import
    And I go to Customers/ Customers
    Then there is no records in grid
    When I download "Customers" Data Template file
    Then I see Name column
    And I fill template with data:
      | Id | Name                      | Parent Id | Group Name          | Tax code           | Account Id | Internal rating Id | Payment term Label | Owner ID |
      |    | Company A                 |           | All Customers       | Tax_code_1         | 1          | 2_of_5             | net 30             |          |
      |    | Company A - East Division | 1         | All Customers       | Tax_code_1         | 2          | 1_of_5             | net 90             |          |
      |    | Company A - West Division | 1         | All Customers       | Tax_code_1         | 3          | 1_of_5             | net 60             |          |
      |    | Customer G                |           | Wholesale Customers | Tax_code_3         | 4          | 3_of_5             | net 60             | 2        |
      |    | Partner C                 |           | Partners            | Tax_code_3         | 5          | 4_of_5             | net 30             | 1        |
      |    | Wholesaler B              |           | All Customers       | Tax_code_undefined | 6          | 4_of_5             | net 60             | 3        |

  Scenario: Import new Customers
    When I import file
    And reload the page
    Then Email should contains the following "Result of importing file" text
    And I follow "Error log" link from the email
    And I should not see "403. Forbidden You don't have permission to access this page."
    # Check Sales Manager permissions
    When I proceed as the Ethan
    And I login as "ethan" user
    And I follow "Error log" link from the email
    Then I should see "403. Forbidden You don't have permission to access this page."
