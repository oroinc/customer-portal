@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Remember me after browser session finished
  ToDo: BAP-16103 Add missing descriptions to the Behat features
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
