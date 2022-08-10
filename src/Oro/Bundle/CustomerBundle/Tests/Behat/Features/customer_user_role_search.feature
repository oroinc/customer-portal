@fixture-OroUserBundle:user.yml

Feature: Customer User Role search
  In order to search Customer User Role
  As an user
  I should see view page of Customer User Role entity in search results with role permissions 'View:Global'
  for Customer User Role entity

  Scenario: Edit view permissions for Customer User Role entity with Sales Rep Role
    Given I login as administrator
    Then go to System / User Management / Roles
    When I filter Label as is equal to "Sales Rep"
    And I click edit "Sales Rep" in grid
    And select following permissions:
      | Customer User Role | View:Global |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Search Customer User Role
    Given I login as "charlie" user
    And I click "Search"
    And type "admin" in "search"
    When I click "Search Submit"
    Then I should be on Search Result page
    And I should see following search entity types:
      | Type               | N | isSelected |
      | Customer User Roles | 1 |            |
    And I should see following search results:
      | Title         | Type               |
      | Administrator | Customer User Role |

  Scenario: View entity from search results
    Given I filter result by "Customer User Roles" type
    Then I follow "Administrator"
    Then I should be on Customer User Role View page
