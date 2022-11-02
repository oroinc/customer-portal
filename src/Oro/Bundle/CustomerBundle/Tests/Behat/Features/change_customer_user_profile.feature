@ticket-BB-17702
@ticket-BAP-20232
@waf-skip
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Change customer user profile
  In order to save profile data
  As a customer user
  I need to make sure that the profile data will be displayed correctly

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Add extended field to the CustomerUser entity
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/Entities/Entity Management
    And I filter Name as is equal to "CustomerUser"
    And I click view CustomerUser in grid
    And I click "Create field"
    And I fill form with:
      | Field name   | TableColumnStringField |
      | Storage type | Table column           |
      | Type         | String                 |
    And I click "Continue"
    When I save and close form
    Then I should see "Field saved" flash message
    When I click update schema
    Then I should see "Schema updated" flash message

  Scenario: Update the CustomerUser entity
    Given I go to Customers/Customer Users
    When I click edit NancyJSallee@example.org in grid
    And I fill form with:
      | TableColumnStringField | Test string |
    And I save and close form
    Then I should see "Customer User has been saved" flash message
    And I should see Customer User with:
      | Name Prefix            | N/A           |
      | Middle Name            | N/A           |
      | Name Suffix            | N/A           |
      | Birthday               | N/A           |
      | Roles                  | Administrator |
      | Website                | Default       |
      | TableColumnStringField | Test string   |

  Scenario: Customer user saves and shows profile data
    Given I proceed as the Buyer
    And I signed in as NancyJSallee@example.org on the store frontend
    And I follow "Account"
    When I click "Edit"
    And I fill "Customer User Profile Form" with:
      | Name Prefix | Prefix<script>alert("Name Prefix")</script> |
      | First Name  | <script>alert("First Name")</script>        |
      | Middle Name | <script>alert("Middle Name")</script>       |
      | Last Name   | <script>alert("Last Name")</script>         |
      | Name Suffix | <script>alert("Name Suffix")</script>Suffix |
      | Birthday    | 5/55/5555                                   |
    Then I should see "This value is not a valid date."
    When I fill "Customer User Profile Form" with:
      | Birthday | 1/2/1954 |
    And I click "Save"
    Then I should see "Customer User profile updated"
    And I should not see "Prefix Suffix"
    And I should see "Birthday 1/2/1954"

  Scenario: Check the CustomerUser entity
    Given I proceed as the Admin
    When I reload the page
    Then I should see Customer User with:
      | Name Prefix            | Prefix<script>alert("Name Prefix")</script> |
      | Middle Name            | <script>alert("Middle Name")</script>       |
      | Name Suffix            | <script>alert("Name Suffix")</script>Suffix |
      | Birthday               | Jan 2, 1954                                 |
      | Roles                  | Administrator                               |
      | Website                | Default                                     |
      | TableColumnStringField | Test string                                 |
