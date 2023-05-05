@feature-BB-21879
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Create customer user by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new customer user from customer view page by quick access button using new tab

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "new_tab"
    And I login as administrator

  Scenario: Create customer user by click on quick access button
    When I go to Customers / Customers
    And I set alias "tab1" for the current browser tab
    And I click view "NoCustomerUser" in grid
    Then I should see following buttons:
      | New Customer User |
    When I click "New Customer User"
    Then a new browser tab is opened and I switch to it
    And "Customer User Form" must contains values:
      | Customer | NoCustomerUser |
    When I fill "Customer User Form" with:
      | First Name         | CustomerUserName1          |
      | Last Name          | CustomerUserLastName1      |
      | Email Address      | customer_user1@example.com |
      | Password           | QsXdR%432!                 |
      | Confirm Password   | QsXdR%432!                 |
      | Buyer (Predefined) | true                       |
    And I save and close form
    Then I should see Customer User with:
      | Name     | CustomerUserName1 |
      | Customer | NoCustomerUser    |

  Scenario: Create customer user by click on quick access button from dropdown
    When I switch to the browser tab "tab1"
    And I follow "More actions"
    And I follow "Create Customer User"
    Then a new browser tab is opened and I switch to it
    And "Customer User Form" must contains values:
      | Customer | NoCustomerUser |
    When I fill "Customer User Form" with:
      | First Name         | CustomerUserName2          |
      | Last Name          | CustomerUserLastName2      |
      | Email Address      | customer_user2@example.com |
      | Password           | QsXdR%432!                 |
      | Confirm Password   | QsXdR%432!                 |
      | Buyer (Predefined) | true                       |
    And I save and close form
    Then I should see Customer with:
      | Name     | CustomerUserName2 |
      | Customer | NoCustomerUser    |

  Scenario: Save customer user and return to customer view page after click on quick access button on page
    When I switch to the browser tab "tab1"
    And I click "New Customer User"
    Then a new browser tab is opened and I switch to it
    And "Customer User Form" must contains values:
      | Customer | NoCustomerUser |
    And I should see "Save and Return" action button
    And I set alias "tab2" for the current browser tab
    When I open a new browser tab and set "tab3" alias for it
    Then I should see "Save and Return" action button
    When I switch to the browser tab "tab2"
    And I fill "Customer User Form" with:
      | First Name         | CustomerUserName3          |
      | Last Name          | CustomerUserLastName3      |
      | Email Address      | customer_user3@example.com |
      | Password           | QsXdR%432!                 |
      | Confirm Password   | QsXdR%432!                 |
      | Buyer (Predefined) | true                       |
    And I save form and return
    Then I should see "Customers / NoCustomerUser"
    And I should see following "Customer User by Customer Grid" grid:
      | First Name        | Last Name             | Email Address              |
      | CustomerUserName1 | CustomerUserLastName1 | customer_user1@example.com |
      | CustomerUserName2 | CustomerUserLastName2 | customer_user2@example.com |
      | CustomerUserName3 | CustomerUserLastName3 | customer_user3@example.com |

  Scenario: Set Customer entity view permissions to 'View:None' for Administrator Role
    When I switch to the browser tab "tab1"
    And I go to System / User Management / Roles
    And I filter Label as is equal to "Administrator"
    And I click edit "Administrator" in grid
    And select following permissions:
      | Customer | View:None |
    And save and close form
    Then I should see "Role saved" flash message

  Scenario: Check save and return action button is not shown on create customer user page
    When I switch to the browser tab "tab3"
    And I reload the page
    Then I should not see "Save and Return" action button
