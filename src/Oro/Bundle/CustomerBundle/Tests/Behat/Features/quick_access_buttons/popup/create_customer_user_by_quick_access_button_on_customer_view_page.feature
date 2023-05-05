@feature-BB-21879
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Create customer user by quick access button on customer view page
  In order to simplify access to most used back-office functionality and speed up data entry
  As an administrator
  I want to create new customer user from customer view page by quick access button using popup dialog

  Scenario: Feature Background
    Given I set configuration property "oro_ui.quick_create_actions" to "popup"
    And I login as administrator

  Scenario: Create customer user by click on quick access button
    When I go to Customers / Customers
    And I click view "NoCustomerUser" in grid
    Then I should see following buttons:
      | New Customer User |
    When I click "New Customer User"
    Then I should see "UiDialog" with elements:
      | Title        | Create Customer User |
      | okButton     | Save                 |
      | cancelButton | Cancel               |
    And "Customer User Form" must contains values:
      | Customer | NoCustomerUser |
    When I fill "Customer User Form" with:
      | First Name         | CustomerUserName1          |
      | Last Name          | CustomerUserLastName1      |
      | Email Address      | customer_user1@example.com |
      | Password           | QsXdR%432!                 |
      | Confirm Password   | QsXdR%432!                 |
      | Buyer (Predefined) | true                       |
    And I click "Save"
    Then I should see following "Customer User by Customer Grid" grid:
      | First Name        | Last Name             | Email Address              |
      | CustomerUserName1 | CustomerUserLastName1 | customer_user1@example.com |

  Scenario: Create customer user by click on quick access button from dropdown
    When I follow "More actions"
    And I follow "Create Customer User"
    Then I should see "UiDialog" with elements:
      | Title        | Create Customer User |
      | okButton     | Save                 |
      | cancelButton | Cancel               |
    And "Customer User Form" must contains values:
      | Customer | NoCustomerUser |
    When I fill "Customer User Form" with:
      | First Name         | CustomerUserName2          |
      | Last Name          | CustomerUserLastName2      |
      | Email Address      | customer_user2@example.com |
      | Password           | QsXdR%432!                 |
      | Confirm Password   | QsXdR%432!                 |
      | Buyer (Predefined) | true                       |
    And I click "Save"
    Then I should see following "Customer User by Customer Grid" grid:
      | First Name        | Last Name             | Email Address              |
      | CustomerUserName1 | CustomerUserLastName1 | customer_user1@example.com |
      | CustomerUserName2 | CustomerUserLastName2 | customer_user2@example.com |
