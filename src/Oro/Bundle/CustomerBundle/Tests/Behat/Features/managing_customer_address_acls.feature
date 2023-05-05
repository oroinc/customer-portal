@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@fixture-OroCustomerBundle:AdminUser.yml
@regression
Feature: Managing customer address ACLs
  In order to control user permissions
  As an Administrator
  I want to be able to set ACL rules for customer addresses and customer user addresses

  Scenario: Feature Background
    Given sessions active:
      | User | first_session  |
      | Admin | second_session |

  Scenario: Ensure permissions are set to allow everything
    Given I proceed as the Admin
    And I login as administrator
    And I go to System / User Management / Roles
    When I click View Administrator in grid
    Then the role has following active permissions:
      | Customer Address      | View:Global | Create:Global | Edit:Global | Delete:Global |

  Scenario: Ensure viewing addresses is allowed
    Given I proceed as the User
    And I login as "charlie" user
    When I go to Customers / Customers
    Then I click on first customer in grid
    And I should see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should see "34500 Capitol Avenue"

  Scenario: Ensure creating addresses is allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I click "New Address"
    Then I fill form with:
      | Label           | Test address 1 |
      | Country         | United States  |
      | Street          | South street   |
      | City            | New city       |
      | State           | Alabama        |
      | Zip/Postal Code | 67726534       |
      | Organization    | Test Org       |
    And I click "Save"
    Then I should see "Address saved" flash message

  Scenario: Ensure editing addresses is allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    And click edit Test address 1 address
    Then I fill form with:
      | Street | East street |
    And I click "Save"
    Then I should see "Address saved" flash message

  Scenario: Ensure deleting addresses is allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    And I delete Test address 1 address
    And I click "Yes, Delete"
    Then I should not see "Test address 1"

  Scenario: Addresses not visible when viewing is not allowed
    Given I proceed as the Admin
    And I go to System / User Management / Roles
    And I click Edit Administrator in grid
    And select following permissions:
      | Customer Address | View:None |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User
    And I go to Customers / Customers
    Then I click on first customer in grid
    Then I should not see "Address Book"
    And I should not see "801 Scenic Hwy"
    And I should not see "23400 Caldwell Road"
    And I should not see "34500 Capitol Avenue"
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | View:Global |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Can't create addresses when creating is not allowed
    Given I proceed as the User
    And I go to Customers / Customers
    Then I click on first customer in grid
    Then I click "New Address"
    Then I fill form with:
      | Label           | Test address 3 |
      | Country         | United States  |
      | Street          | South street   |
      | City            | New city       |
      | State           | Alabama        |
      | Zip/Postal Code | 67726534       |
      | Organization    | Test Org       |
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Create:None |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User
    And I click "Save"
    Then I should see "You do not have permission to perform this action" error message

  Scenario: New address button not visible when creating is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I should see "Address Book"
    And I should not see "New Address"
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Create:Global |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User

  Scenario: Can't edit addresses when editing is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    And click edit Address 1 address
    Then I fill form with:
      | Street | East street |
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Edit:None |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User
    And I click "Save"
    Then I should see "You do not have permission to perform this action" error message

  Scenario: Edit address button not visible when editing is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I should see "Address Book"
    And I should not see an "Edit Address Button" element
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Edit:Global |
      | Customer Address | Delete:None |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User

  Scenario: Delete address button not visible when deleting is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I should see "Address Book"
    And I should not see an "Delete Address Button" element
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Delete:Global |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User

  Scenario: Can't edit address when editing is not allowed on customer page
    Given I go to Customers / Customers
    Then I click edit first customer in grid
    Then I fill form with:
      | Street | East street |
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Edit:None |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User
    And I save form
    Then I should see "This form should not contain extra fields" error message

  Scenario: When editing not allowed we don't see address block in customer page
    And I go to Customers / Customers
    Then I click edit first customer in grid
    And I should not see "Addresses"
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Edit:Global |
      | Customer Address | Delete:None |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User

  Scenario: Delete address button not visible when deleting is not allowed in Customer edit page
    And I go to Customers / Customers
    Then I click edit first customer in grid
    And I should see "Addresses"
    And I should not see an "Delete Address Button Edit Page" element
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Delete:Global |
      | Customer Address | Create:None |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User

  Scenario: Create address button not visible when create is not allowed in Customer edit page
    And I go to Customers / Customers
    Then I click edit first customer in grid
    And I should see "Addresses"
    And I should not see an "Add Address Button Edit Page" element
    When I proceed as the Admin
    And I click "Edit"
    And select following permissions:
      | Customer Address | Create:Global |
    And save and close form
    Then I should see "Role saved" flash message
    And I proceed as the User
