@ticket-BB-8128
@automatically-ticket-tagged
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Customer user roles crud frontend
  In order to control customer user permissions
  As an Buyer
  I want to be able to manage user roles

  Scenario: Customer user role create
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Roles"
    When I click "Create Customer User Role"
    And I fill form with:
      | Role Title | Test customer user role |
    And select following permissions:
      | Customer User Address | View:User | Create:Department | Edit:Сorporate |
    And I click "Create"
    Then I should see "Test customer user role"

  Scenario: Disable configuring permissions and capabilities for user role
    And I click "Account"
    And I click "Roles"
    When I click View Test customer user role in grid
    Then the role has following active permissions:
      | Customer User Address | View:User (Own) | Create:Department (Same Level) | Edit:Сorporate (All Levels) |
    And I should see "Share data view"
    When permission VIEW for entity Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress and group commerce_frontend restricts in application
    And permission CREATE for entity Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress and group commerce_frontend restricts in application
    And capability oro_customer_frontend_gridview_publish and group commerce_frontend restricts in application
    And I reload the page
    Then the role has not following permissions:
      | Customer User Address | View | Create |
    And I should not see "Share data view"
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
