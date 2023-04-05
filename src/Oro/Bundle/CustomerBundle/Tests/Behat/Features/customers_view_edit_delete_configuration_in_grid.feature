@feature-BB-22240
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Customers view edit delete configuration in grid
  As an administrator
  I want to view/edit/delete/configure items in grid on customers page

  Scenario: Not able to edit and configure customer settings in grid on customers page without edit permission
    Given I login as administrator
    When I go to System/ User Management/ Roles
    And I click edit Administrator in grid
    And select following permissions:
      | Customer | Edit:None |
    And I save and close form
    Then I should see "Role saved" flash message

    When I go to Customers / Customers
    Then I should see WithCustomerUser in grid
    And I should not see following actions for WithCustomerUser in grid:
      | Edit |
      | Configuration |

    When I go to System/ User Management/ Roles
    And I click edit Administrator in grid
    And select following permissions:
      | Customer | Edit:Global |
    And I save and close form
    Then I should see "Role saved" flash message

    When I go to Customers / Customers
    And I should see following actions for WithCustomerUser in grid:
      | Edit |
      | Configuration |
