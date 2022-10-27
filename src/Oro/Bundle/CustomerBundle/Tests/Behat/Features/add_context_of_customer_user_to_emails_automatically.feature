@ticket-BB-19206
@fixture-OroUserBundle:user.yml
@fixture-OroCustomerBundle:CustomerUserFixture.yml
@elasticsearch

Feature: Add context of customer user to emails automatically
  In order to have ability to send email
  As an Administrator
  I would like to have the application automatically add the customer user as a context record to my emails.

  Scenario: Send email for unknown address
    Given I login as administrator
    And I click My Emails in user menu
    And there is no records in grid
    And I click "Compose"
    When I fill "Email Form" with:
      | To      | test@mydomain.myzone |
      | Subject | Test Subject 1       |
      | Body    | Test Body 1          |
    And I click "Send"
    Then I should see "The email was sent" flash message

  Scenario: Check email for unknown address
    When I click view test@mydomain.myzone in grid
    Then I should not see "test@mydomain.myzone" in the "Email Page Contexts" element

  Scenario: Send email for customer user address
    Given I click My Emails in user menu
    And I click "Compose"
    When I fill "Email Form" with:
      | To      | Amanda Cole    |
      | Subject | Test Subject 2 |
      | Body    | Test Body 2    |
    And I click "Send"
    Then I should see "The email was sent" flash message

  Scenario: Check email for customer user address
    When I click view Amanda Cole in grid
    Then I should see "Amanda Cole" in the "Email Page Contexts" element
