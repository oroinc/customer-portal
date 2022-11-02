@ticket-BB-9092
@regression
@fixture-OroNotificationBundle:NotifyAssignedSalesRepsFixture.yml
@fixture-OroCustomerBundle:CustomerUserAmandaRCole.yml
Feature: Notify assigned Sales Representatives
  As an Administrator
  When I create email notification for Contact Request
  I wan't select assigned Sales Reps from Contact Request to be notified.

  Scenario: Assign Sales Representatives to Amanda Cole
    Given I login as administrator
    And I go to Customers / Customer Users
    And I click Edit "AmandaRCole@example.org" in grid
    And I fill form with:
      | Assigned Sales Representatives | [Megan Fox] |
    When I save and close form
    Then I should see "Customer User has been saved" flash message

  Scenario: Create email template
    Given go to System/ Emails/ Templates
    And click "Create Email Template"
    And fill form with:
      | Owner         | John Doe        |
      | Template Name | Test Template   |
      | Type          | Html            |
      | Entity Name   | Contact Request |
      | Subject       | Test Subject    |
      | Content       | Test Content    |
    When I save and close form
    Then I should see "Template saved" flash message

  Scenario: Create notification rule
    Given go to System/ Emails/ Notification Rules
    And click "Create Notification Rule"
    And fill form with:
      | Entity Name             | Contact Request                                |
      | Event Name              | Entity create                                  |
      | Template                | Test Template                                  |
      | Additional Associations | Customer User > Assigned Sales Representatives |
    When I save and close form
    Then I should see "Notification Rule saved" flash message

  Scenario: Create contact request
    Given go to Activities/ Contact Requests
    And click "Create Contact Request"
    And fill form with:
      | First Name               | Test          |
      | Last Name                | Testerson     |
      | Preferred contact method | Email         |
      | Email                    | test@test.com |
      | Comment                  | Test comment  |
      | Customer User            | Amanda Cole   |
    When I save and close form
    Then I should see "Contact request has been saved successfully" flash message
    And Email should contains the following:
      | Subject | Test Subject   |
      | To      | megan@test.com |
      | Body    | Test Content   |
