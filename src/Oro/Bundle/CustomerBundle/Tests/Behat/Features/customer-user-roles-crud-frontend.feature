@fixture-BuyerCustomerFixture.yml
Feature: Managing customer user roles
  In order to control customer user permissions
  As an Buyer
  I want to be able to manage user roles

  Scenario: Customer user role create
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Roles"
    When I press "Create Customer User Role"
    And I fill form with:
      | Role Title | Test customer user role |
    And select following permissions:
      | Customer User Address | View:User | Create:Department | Edit:Сorporate |
    And I press "Create"
    Then I should see "Test customer user role"

  Scenario: Disable configuring permissions and capabilities for user role
    And I click "Account"
    And I click "Roles"
    When I click View Test customer user role in grid
    Then the role has following active permissions:
      | Customer User Address | View:User (Own) | Create:Department (Same Level) | Edit:Сorporate (All Levels) |
    And I should see "Audit history for Customer User"
    When permission VIEW for entity Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress and group commerce_frontend restricts in application
    And permission CREATE for entity Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress and group commerce_frontend restricts in application
    And capability oro_customer_dataaudit_history and group commerce_frontend restricts in application
    And I reload the page
    Then the role has not following permissions:
      | Customer User Address | View | Create |
    And I should not see "Audit history for Customer User"
    When all permissions for entity Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress and group commerce_frontend restricts in application
    And I should see "Customer User Address"
    And I reload the page
    Then I should not see "Customer User Address"

  Scenario: Delete user role
    And I click "Account"
    And I click "Roles"
    Then I should see Test customer user role in grid
    When I click Delete Test customer user role in grid
    Then I should not see "Test customer user role"
