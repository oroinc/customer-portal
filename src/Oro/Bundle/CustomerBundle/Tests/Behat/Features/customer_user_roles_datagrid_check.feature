@ticket-BB-16923
@ticket-BB-20785
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Customer user roles datagrid check
  In order to check datagrid manager
  As an User
  I should be able to manage datagrid by datagrid manager

  Scenario: Customer user role check datagrid manager
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Roles"
    And I click Edit Administrator in grid
    When I click on "FrontendGridColumnManagerButton"
    Then I should see an "FrontendGridColumnManager" element

  Scenario: Customer user role check datagrid filter manager position
    When I click on "Frontend Grid Action Filter Button"
    Then I should see an "Customer Roles Users Update Grid GridFilters" element
