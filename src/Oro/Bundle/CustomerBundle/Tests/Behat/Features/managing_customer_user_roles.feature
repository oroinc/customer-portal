@ticket-BB-8128
@automatically-ticket-tagged
Feature: Managing customer user roles
  In order to control customer user permissions
  As an Administrator
  I want to be able to manage user roles

  Scenario: Customer user role create
    Given I login as administrator
    And go to Customers/Customer User Roles
    When I click "Create Customer User Role"
    And I fill form with:
      | Role | Test customer user role |
    And select following permissions:
      | Customer Group | View:Full | Create:Full | Edit:Full | Delete:Full |
    And I save and close form
    Then I should see "Test customer user role"

  Scenario: Disable configuring permissions and capabilities for user role
    And I go to Customers/Customer User Roles
    When I click View Test customer user role in grid
    Then the role has following active permissions:
      | Customer Group | View:Full | Create:Full | Edit:Full | Delete:Full |
    And I should see "Share data view"
    When permission VIEW for entity Oro\Bundle\CustomerBundle\Entity\CustomerGroup and group commerce restricts in application
    And permission CREATE for entity Oro\Bundle\CustomerBundle\Entity\CustomerGroup and group commerce restricts in application
    And capability oro_customer_frontend_gridview_publish and group commerce restricts in application
    And I reload the page
    Then the role has not following permissions:
      | Customer Group | View | Create |
    And I should not see "Share data view"
    When all permissions for entity Oro\Bundle\CustomerBundle\Entity\CustomerGroup and group commerce restricts in application
    And I should see "Customer Group"
    And I reload the page
    Then I should not see "Customer Group"

  Scenario: Delete user role
    Given I go to Customers/Customer User Roles
    Then I should see Test customer user role in grid
    And I keep in mind number of records in list
    When I click Delete Test customer user role in grid
    And I confirm deletion
    Then the number of records decreased by 1
    And I should not see "Test customer user role"
