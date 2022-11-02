@ticket-BB-15502
@fixture-OroCustomerBundle:CustomerUserFixture.yml
Feature: Cancel editing account info
  As Frontend User
  I should be redirected to previous page after canceling editing account info
  Scenario: Cancel editing account info
    Given I signed in as AmandaRCole@example.org on the store frontend
    When I follow "Account"
    And I click "Edit Profile Button"
    And I click "Cancel"
    Then the url should match "/customer/profile"
