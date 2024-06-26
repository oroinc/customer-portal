@regression
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Remember me after browser session finished

  Scenario: Customer user logs in ticking "Remember me". As he closes his browser and visits back the site, he should be automatically logged in.
    Given I am on the homepage
    When I follow "Log In"
    And I fill "Customer Login Form" with:
      |Email       | AmandaRCole@example.org |
      |Password    | AmandaRCole@example.org |
      |Remember Me | true                    |
    And I click "Log In Button"
    Then I should see "Amanda Cole"
    When I restart the browser
    Then I should see "Amanda Cole"

  Scenario: Customer user must be logged after deleting session for the customer profile page
    And I click "Account Dropdown"
    And I click "My Profile"
    And I should see "Account Info"
    When I restart the browser
    Then I should see "Amanda Cole"
    And I should see "Account"
