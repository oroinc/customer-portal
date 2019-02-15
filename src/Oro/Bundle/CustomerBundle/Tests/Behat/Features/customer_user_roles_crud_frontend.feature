@ticket-BB-8128
@ticket-BB-15477
@automatically-ticket-tagged
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroCustomerBundle:CustomerUserRoleFixture.yml
@fixture-OroOrganizationProBundle:SecondOrganizationFixture.yml

Feature: Customer user roles crud frontend
  In order to control customer user permissions
  As an Buyer
  I want to be able to manage user roles

  Scenario: Customer user should not see not self-managed roles in grid list
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Roles"
    Then I should see following grid:
      | Role                               | Type         |
      | Administrator                      | Predefined   |
      | Buyer                              | Predefined   |
      | customer assigned self managed     | Customizable |
      | customer not assigned self managed | Predefined   |
    And I should not see "customer assigned not self managed"
    And I should not see "not customer not assigned not self managed"

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

  Scenario: Create Customer User Role in second Organization
    Given I login as administrator
    And I am logged in under ORO Pro organization
    And I go to Customers / Customer User Roles
    And I click "Create Customer User Role"
    And I fill form with:
      | Role | Buyer |
    When I save and close form
    Then I should see "Customer User Role has been saved"

  Scenario: Validate Customer User Role in second Organization
    Given I go to Customers / Customer User Roles
    And I click "Create Customer User Role"
    And I fill form with:
      | Role | Buyer |
    When I save and close form
    Then I should see validation errors:
      | Role | Role with this name already exists |
