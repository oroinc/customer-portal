@ticket-BB-19206
@ticket-BAP-22197
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
    When I click "Compose"
    And I fill "Email Form" with:
      | To      | test@mydomain.myzone |
      | Subject | Test Subject 1       |
      | Body    | Test Body 1          |
    And I click "Send"
    Then I should see "The email was sent" flash message
    When I click view Test Subject 1 in grid
    Then I should not see "test@mydomain.myzone" in the "Email Page Contexts" element

  Scenario: Send email for customer user address
    Given I click My Emails in user menu
    When I click "Compose"
    And I fill "Email Form" with:
      | To      | Amanda Cole    |
      | Subject | Test Subject 2 |
      | Body    | Test Body 2    |
    And I click "Send"
    Then I should see "The email was sent" flash message
    When I click view Test Subject 2 in grid
    Then I should not see "Amanda Cole" in the "Email Page Contexts" element

  Scenario: Send email for customer user address when customer user is added to email context
    Given I click My Emails in user menu
    When I click "Compose"
    And I fill "Email Form" with:
      | To       | Amanda Cole    |
      | Subject  | Test Subject 3 |
      | Body     | Test Body 3    |
      | Contexts | [Amanda Cole]  |
    And I click "Send"
    Then I should see "The email was sent" flash message
    When I click view Test Subject 3 in grid
    Then I should see "Amanda Cole" in the "Email Page Contexts" element
