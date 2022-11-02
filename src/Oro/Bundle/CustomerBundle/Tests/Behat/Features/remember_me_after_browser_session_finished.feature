@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Remember me after browser session finished

  Scenario: Customer user logs in ticking "Remember me". As he closes his browser and visits back the site, he should be automatically logged in.
    Given I am on the homepage
    When I follow "Sign In"
    And I fill form with:
      |Email Address |AmandaRCole@example.org|
      |Password      |AmandaRCole@example.org|
      |Remember Me   |true                   |
    And I click "Sign In"
    Then I should see "Signed in as: Amanda Cole"
    When I restart the browser
    Then I should see "Signed in as: Amanda Cole"

  Scenario: Customer user must be logged after deleting session for the customer profile page
    Given I follow "Account"
    And I should see "Account info"
    When I restart the browser
    Then I should see "Signed in as: Amanda Cole"
    And I should see "Account"
