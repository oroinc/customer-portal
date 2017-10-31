@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Access user profile

  Scenario: Redirect to login when not logged-in user try to access user profile page
    Given I am on "customer/profile"
    Then Page title equals to "Sign In"
    And I signed in as NancyJSallee@example.org on the store frontend
    Then I should see "My Profile"
