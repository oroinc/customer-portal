@regression
@ticket-BB-21246
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
# Suggesting that OroProductBundle:Tests\Behat\Features\product\many_to_many_product_entity_extend_relation.feature
# could be removed because it test what looks only for many to many relations fields by creating product.
# Now this behat test is testing not only many to many relations fields but also ticket BB-21246's bug.

Feature: Many to many multiple entity field initial validation
  In order to allow users to link entities to product entity via extend relations
  As an Administrator
  I want to create extend many to many relation for product entity

  Scenario: Feature Background
    Given I login as administrator
    And go to System/Entities/Entity Management

  Scenario: Can create Many to many extend relation field
    Given I filter Name as is equal to "CustomerUser"
    And click View CustomerUser in grid
    And click "Create Field"
    And fill form with:
      | Field name | userEmailTemplates |
      | Type       | Many to many       |
    And click "Continue"
    When I fill form with:
      | Target entity              | Email Template |
      | Related entity data fields | Id             |
      | Related entity info title  | [Subject]      |
      | Related entity detailed    | [Content]      |
    And I save and close form
    Then I should see "Field saved" flash message

    When I click update schema
    Then I should see "Schema updated" flash message

  Scenario: Populate created relation field and observe after failed validation
    Given go to Customers / Customer Users
    And I click "Create Customer User"
    And fill form with:
      | First Name        | John                |
      | Last Name         | Doe                 |
      | Email Address     | john.doe@oroinc.com |
      | Customer          | first customer      |
      | Generate Password | true                |
    And press "Add" in "Additional" section
    And I select following records in grid:
      | OAuth application added to your account |
      | Deactivation Notice                     |
      | Please change your password             |
    And I click "Select"
    And save and close form
    And I should see "Please select at least one role before you enable the customer user"
    And I should see "OAuth application added to your account"
    And I should see "Deactivation Notice"
    And I should see "Please change your password"
    When I fill form with:
      | Roles       | Buyer  |
    And save and close form
    Then I should see "Customer User has been saved" flash message
