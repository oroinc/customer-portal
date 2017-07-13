@fixture-OroCustomerBundle:CustomerUserAddressFixture.yml
@regression
Feature: Managing customer address and customer user address ACLs
  In order to control user permissions
  As an Administrator
  I want to be able to set ACL rules for customer addresses and customer user addresses

  Scenario: Ensure permissions are set to allow everything
    Given I login as administrator
    And I go to System / User Management / Roles
    When I click View Administrator in grid
    Then the role has following active permissions:
      | Customer Address      | View:Global | Create:Global | Edit:Global | Delete:Global |
      | Customer User Address | View:Global | Create:Global | Edit:Global | Delete:Global |

  Scenario: Ensure viewing addresses is allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    And I should see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should see "34500 Capitol Avenue"

    Then I go to Customers / Customer Users
    Then I click on Amanda in grid
    And I should see "801 Scenic Hwy"
    And I should see "23400 Caldwell Road"
    And I should see "34500 Capitol Avenue"

  Scenario: Ensure creating addresses is allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I press "+ New Address"
    Then I fill form with:
      | Label           | Test address 1 |
      | Country         | United States  |
      | Street          | South street   |
      | City            | New city       |
      | State           | Alabama        |
      | Zip/Postal Code | 67726534       |
    And I press "Save"
    Then I should see "Address saved" flash message

    Then I go to Customers / Customer Users
    Then I click on Amanda in grid
    Then I press "+ New Address"
    Then I fill form with:
      | Label           | Test address 2 |
      | Country         | United States  |
      | Street          | North street   |
      | City            | New city       |
      | State           | Alabama        |
      | Zip/Postal Code | 677262368      |
    And I press "Save"
    Then I should see "Address saved" flash message

  Scenario: Ensure editing addresses is allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    And click edit Test address 1 address
    Then I fill form with:
      | Street | East street |
    And I press "Save"
    Then I should see "Address saved" flash message

    Given I go to Customers / Customer Users
    Then I click on Amanda in grid
    And click edit Test address 2 address
    Then I fill form with:
      | Street | West street |
    And I press "Save"
    Then I should see "Address saved" flash message

  Scenario: Ensure deleting addresses is allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    And I delete Test address 1 address
    And I press "Yes, Delete"
    Then I should not see "Test address 1"

    Given I go to Customers / Customer Users
    Then I click on Amanda in grid
    And I delete Test address 2 address
    And I press "Yes, Delete"
    Then I should not see "Test address 2"

  Scenario: Addresses not visible when viewing is not allowed
    Given administrator have "None" permissions for "View" "Customer Address" entity
    And I go to Customers / Customers
    Then I click on first customer in grid
    Then I should not see "Address Book"
    And I should not see "801 Scenic Hwy"
    And I should not see "23400 Caldwell Road"
    And I should not see "34500 Capitol Avenue"
    Then administrator have "Global" permissions for "View" "Customer Address" entity

    Given administrator have "None" permissions for "View" "Customer User Address" entity
    And I go to Customers / Customer Users
    Then I click on Amanda in grid
    Then I should not see "Address Book"
    And I should not see "801 Scenic Hwy"
    And I should not see "23400 Caldwell Road"
    And I should not see "34500 Capitol Avenue"
    Then administrator have "Global" permissions for "View" "Customer User Address" entity

  Scenario: Can't create addresses when creating is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I press "+ New Address"
    Then I fill form with:
      | Label           | Test address 3 |
      | Country         | United States  |
      | Street          | South street   |
      | City            | New city       |
      | State           | Alabama        |
      | Zip/Postal Code | 67726534       |
    Given administrator have "None" permissions for "Create" "Customer Address" entity
    And I press "Save"
    Then I should see "You do not have permission to perform this action" error message

    Given I go to Customers / Customer Users
    Then I click on Amanda in grid
    Then I press "+ New Address"
    Then I fill form with:
      | Label           | Test address 4 |
      | Country         | United States  |
      | Street          | South street   |
      | City            | New city       |
      | State           | Alabama        |
      | Zip/Postal Code | 67726534       |
    Given administrator have "None" permissions for "Create" "Customer User Address" entity
    And I press "Save"
    Then I should see "You do not have permission to perform this action" error message

  Scenario: New address button not visible when creating is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I should see "Address Book"
    And I should not see "+ New Address"
    Given administrator have "Global" permissions for "Create" "Customer Address" entity

    Given I go to Customers / Customer Users
    Then I click on Amanda in grid
    Then I should see "Address Book"
    And I should not see "+ New Address"
    Given administrator have "Global" permissions for "Create" "Customer User Address" entity

  Scenario: Can't edit addresses when editing is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    And click edit Address 1 address
    Then I fill form with:
      | Street | East street |
    Given administrator have "None" permissions for "Edit" "Customer Address" entity
    And I press "Save"
    Then I should see "You do not have permission to perform this action" error message

    Given I go to Customers / Customer Users
    Then I click on Amanda in grid
    And click edit Address 1 address
    Then I fill form with:
      | Street | East street |
    Given administrator have "None" permissions for "Edit" "Customer User Address" entity
    And I press "Save"
    Then I should see "You do not have permission to perform this action" error message

  Scenario: Edit address button not visible when editing is not allowed
    Given I go to Customers / Customers
    Then I click on first customer in grid
    Then I should see "Address Book"
    And I should not see an "Edit Address Button" element
    Given administrator have "Global" permissions for "Edit" "Customer Address" entity

    Given I go to Customers / Customer Users
    Then I click on Amanda in grid
    Then I should see "Address Book"
    And I should not see an "Edit Address Button" element
    Given administrator have "Global" permissions for "Edit" "Customer User Address" entity

  Scenario: Delete address button not visible when deleting is not allowed
    Given administrator have "None" permissions for "Delete" "Customer Address" entity
    And I go to Customers / Customers
    Then I click on first customer in grid
    Then I should see "Address Book"
    And I should not see an "Delete Address Button" element
    Given administrator have "Global" permissions for "Delete" "Customer Address" entity

    Given administrator have "None" permissions for "Delete" "Customer User Address" entity
    And I go to Customers / Customer Users
    Then I click on Amanda in grid
    Then I should see "Address Book"
    And I should not see an "Delete Address Button" element
    Given administrator have "Global" permissions for "Delete" "Customer User Address" entity

  Scenario: Can't edit address when editing is not allowed on customer page
    Given I go to Customers / Customers
    Then I click edit first customer in grid
    Then I fill form with:
      | Street | East street |
    Given administrator have "None" permissions for "Edit" "Customer Address" entity
    And I save form
    Then I should see "This form should not contain extra fields" error message

    Given I go to Customers / Customer Users
    Then I click edit Amanda in grid
    Then I fill form with:
      | Street | East street |
    Given administrator have "None" permissions for "Edit" "Customer User Address" entity
    And I save form
    Then I should see "This form should not contain extra fields" error message

  Scenario: When editing not allowed we don't see address block in customer page
    Given administrator have "None" permissions for "Edit" "Customer Address" entity
    And I go to Customers / Customers
    Then I click edit first customer in grid
    And I should not see "Addresses"
    Given administrator have "Global" permissions for "Edit" "Customer Address" entity

    Given administrator have "None" permissions for "Edit" "Customer User Address" entity
    And I go to Customers / Customer Users
    Then I click edit Amanda in grid
    And I should not see "Addresses"
    Given administrator have "Global" permissions for "Edit" "Customer User Address" entity

  Scenario: Delete address button not visible when deleting is not allowed in Customer edit page
    Given administrator have "None" permissions for "Delete" "Customer Address" entity
    And I go to Customers / Customers
    Then I click edit first customer in grid
    And I should see "Addresses"
    And I should not see an "Delete Address Button Edit Page" element
    Given administrator have "Global" permissions for "Delete" "Customer Address" entity

    Given administrator have "None" permissions for "Delete" "Customer User Address" entity
    And I go to Customers / Customer Users
    Then I click edit Amanda in grid
    And I should see "Addresses"
    And I should not see an "Delete Address Button Edit Page" element
    Given administrator have "Global" permissions for "Delete" "Customer User Address" entity

  Scenario: Create address button not visible when create is not allowed in Customer edit page
    Given administrator have "None" permissions for "Create" "Customer Address" entity
    And I go to Customers / Customers
    Then I click edit first customer in grid
    And I should see "Addresses"
    And I should not see an "Add Address Button Edit Page" element
    Given administrator have "Global" permissions for "Create" "Customer Address" entity

    Given administrator have "None" permissions for "Create" "Customer User Address" entity
    And I go to Customers / Customer Users
    Then I click edit Amanda in grid
    And I should see "Addresses"
    And I should not see an "Add Address Button Edit Page" element
    Given administrator have "Global" permissions for "Create" "Customer User Address" entity
