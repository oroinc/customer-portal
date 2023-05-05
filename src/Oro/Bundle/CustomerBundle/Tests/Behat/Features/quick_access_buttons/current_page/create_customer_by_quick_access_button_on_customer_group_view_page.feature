@feature-BB-21879

Feature: Create customer by quick access button on customer group view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new customer from customer group view page by quick access button using current page

  Scenario: Create customer by click on quick access button
    Given I login as administrator
    When I go to Customers / Customer Groups
    And I click view "Non-Authenticated Visitors" in grid
    Then I should see following buttons:
      | New Customer |
    When I click "New Customer"
    Then "Customer Form" must contains values:
      | Group | Non-Authenticated Visitors |
    When I fill "Customer Form" with:
      | Name | CustomerName1 |
    And I save and close form
    Then I should see Customer with:
      | Name  | CustomerName1              |
      | Group | Non-Authenticated Visitors |

  Scenario: Create customer by click on quick access button in dropdown on customer group view page
    When I go to Customers / Customer Groups
    And I click view "Non-Authenticated Visitors" in grid
    And I click "Create Customer"
    Then "Customer Form" must contains values:
      | Group | Non-Authenticated Visitors |
    When I fill "Customer Form" with:
      | Name | CustomerName2 |
    And I save and close form
    Then I should see Customer with:
      | Name  | CustomerName2              |
      | Group | Non-Authenticated Visitors |

  Scenario: Save customer and return to customer group view page after click on quick access button on page
    When I go to Customers / Customer Groups
    And I click view "Non-Authenticated Visitors" in grid
    And I click "New Customer"
    Then "Customer Form" must contains values:
      | Group | Non-Authenticated Visitors |
    And I should see "Save and Return" action button
    And I set alias "tab1" for the current browser tab
    When I open a new browser tab and set "tab2" alias for it
    Then I should see "Save and Return" action button
    When I switch to the browser tab "tab1"
    And I fill "Customer Form" with:
      | Name | CustomerName3 |
    And I save form and return
    Then I should see "Customer Groups / Non-Authenticated Visitors"
    And I should see following "Customer by Customer Group Grid" grid:
      | Name          | Parent Customer  | Account       |
      | CustomerName1 |                  | CustomerName1 |
      | CustomerName2 |                  | CustomerName2 |
      | CustomerName3 |                  | CustomerName3 |

  Scenario: Set Customer Group entity view permissions to 'View:None' for Administrator Role
    When I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    And I click edit "Administrator" in grid
    And select following permissions:
      | Customer Group | View:None |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Check save and return action button is not shown on customer create page
    When I switch to the browser tab "tab2"
    And I reload the page
    Then I should not see "Save And Return" action button
