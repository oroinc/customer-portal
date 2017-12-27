@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Change customer user profile
  In order to save profile data
  As customer user
  I need to make sure that the profile data will be displayed correctly

  Scenario: Customer user saves and shows profile data
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account"
    And I click "Edit"
    And I fill form with:
      | Name Prefix | Prefix<script>alert("Name Prefix")</script> |
      | First Name  | <script>alert("First Name")</script>        |
      | Middle Name | <script>alert("Middle Name")</script>       |
      | Last Name   | <script>alert("Last Name")</script>         |
      | Name Suffix | <script>alert("Name Suffix")</script>Suffix |
    And I click "Save"
    Then I should see "Customer User profile updated"
    And I should not see "Prefix Suffix"
